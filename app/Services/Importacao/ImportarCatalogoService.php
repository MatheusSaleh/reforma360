<?php

declare(strict_types=1);

namespace App\Services\Importacao;

use App\Enums\StatusImportacao;
use App\Enums\TipoNomenclatura;
use App\Importers\CatalogImporterResolver;
use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
use App\Importers\ImportadorDeDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Models\Importacao;
use App\Rules\Catalogo\RegrasProduto;
use App\Services\Catalogo\ReferenciasFiscais;
use App\Support\NormalizadorFiscal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Processa um arquivo de catálogo: valida linha a linha, grava os produtos válidos
 * (atualizando pela descrição) e registra os erros com o número da linha.
 *
 * Erros de linha não interrompem a importação. Erros do arquivo inteiro (cabeçalho,
 * vazio, linhas demais, XML inválido) desfazem tudo e marcam a importação como falha.
 *
 * Linhas sem CST e cClassTrib (nota no leiaute antigo) usam os de um produto do
 * catálogo com o mesmo NCM/NBS. Cada linha importada fica registrada em
 * importacao_produtos com o valor dela (na nota: o valor do item).
 */
final readonly class ImportarCatalogoService
{
    /**
     * Limite de erros gravados por importação (a contagem continua exata).
     */
    public const int MAXIMO_ERROS_GRAVADOS = 1000;

    public function __construct(
        private CatalogImporterResolver $importadores,
        private DadosAbertos $dadosAbertos,
    ) {}

    /**
     * @throws CalculadoraIndisponivelException transitório: o job tenta de novo
     */
    public function processar(Importacao $importacao): void
    {
        $this->iniciar($importacao);

        $referencias = new ReferenciasFiscais($this->dadosAbertos, CarbonImmutable::today('America/Sao_Paulo'));
        $catalogo = new ClassificacaoPeloCatalogo($importacao->empresa);
        $caminho = (string) $importacao->caminho_arquivo;

        try {
            DB::transaction(function () use ($importacao, $referencias, $catalogo, $caminho): void {
                $importador = $this->importadores->para($importacao->formato);

                if ($importador instanceof ImportadorDeDocumentoFiscal) {
                    $documento = $importador->documento($caminho);
                    $importacao->update([
                        'tipo_documento'   => $documento->tipo,
                        'numero_documento' => mb_substr($documento->numero, 0, 20),
                        'data_documento'   => $documento->data->toDateString(),
                        'uf'               => $documento->uf,
                        'municipio'        => $documento->municipio,
                    ]);
                }

                $importador->ler(
                    $caminho,
                    fn (array $lote) => $this->processarLote($importacao, $lote, $referencias, $catalogo),
                );

                $importacao->update(['status' => StatusImportacao::Concluida, 'concluida_em' => now()]);
            });
        } catch (ArquivoInvalidoException $e) {
            $this->falhar($importacao, $e->getMessage());

            return;
        }

        $this->apagarArquivo($importacao);
    }

    public function falhar(Importacao $importacao, string $mensagem): void
    {
        $importacao->erros()->delete();
        $importacao->itens()->delete();
        $importacao->update([
            'status'            => StatusImportacao::Falhou,
            'mensagem_erro'     => mb_substr($mensagem, 0, 500),
            'total_linhas'      => 0,
            'linhas_importadas' => 0,
            'linhas_com_erro'   => 0,
            'concluida_em'      => now(),
        ]);

        $this->apagarArquivo($importacao);
    }

    private function iniciar(Importacao $importacao): void
    {
        $importacao->erros()->delete();
        $importacao->itens()->delete();
        $importacao->update([
            'tipo_documento'    => null,
            'numero_documento'  => null,
            'data_documento'    => null,
            'uf'                => null,
            'municipio'         => null,
            'status'            => StatusImportacao::Processando,
            'iniciada_em'       => now(),
            'concluida_em'      => null,
            'mensagem_erro'     => null,
            'total_linhas'      => 0,
            'linhas_importadas' => 0,
            'linhas_com_erro'   => 0,
        ]);
    }

    /**
     * @param  list<LinhaPlanilha>  $lote
     */
    private function processarLote(Importacao $importacao, array $lote, ReferenciasFiscais $referencias, ClassificacaoPeloCatalogo $catalogo): void
    {
        $erros = [];
        $itens = [];
        $importadas = 0;
        $comErro = 0;

        foreach ($lote as $linha) {
            $campos = self::normalizar($linha->campos);
            $semClassificacao = $campos['cst'] === null && $campos['c_class_trib'] === null;

            if ($semClassificacao) {
                $campos = [...$campos, ...($catalogo->para($campos['ncm'], $campos['nbs']) ?? [])];
            }

            $errosDaLinha = $this->validar($campos, $referencias);

            if ($errosDaLinha !== []) {
                $comErro++;

                foreach ($errosDaLinha as $erro) {
                    $erros[] = ['importacao_id' => $importacao->id, 'linha' => $linha->linha] + $erro;
                }

                continue;
            }

            $dadosDoProduto = array_diff_key($campos, ['valor_documento' => true]);
            $produto = $importacao->empresa->produtos()->updateOrCreate(['descricao' => $campos['descricao']], $dadosDoProduto);
            $catalogo->lembrar($campos['ncm'], $campos['nbs'], (string) $campos['cst'], (string) $campos['c_class_trib']);

            $itens[] = [
                'importacao_id' => $importacao->id,
                'produto_id'    => $produto->id,
                'linha'         => $linha->linha,
                'valor'         => $campos['valor_documento'],
            ];
            $importadas++;
        }

        if ($itens !== []) {
            $importacao->itens()->insert($itens);
        }

        $vagas = self::MAXIMO_ERROS_GRAVADOS - $importacao->erros()->count();

        if ($vagas > 0 && $erros !== []) {
            $importacao->erros()->insert(array_slice($erros, 0, $vagas));
        }

        Importacao::query()->whereKey($importacao->id)->incrementEach([
            'total_linhas'      => count($lote),
            'linhas_importadas' => $importadas,
            'linhas_com_erro'   => $comErro,
        ]);
    }

    /**
     * @param  array{descricao: string|null, ncm: string|null, nbs: string|null, cst: string|null, c_class_trib: string|null, preco: string|null, valor_documento: string|null}  $campos
     * @return list<array{campo: string|null, mensagem: string}>
     */
    private function validar(array $campos, ReferenciasFiscais $referencias): array
    {
        // Na planilha o valor da linha é o próprio preço: só valida quando vem separado (XML).
        $regraDoValor = $campos['valor_documento'] === $campos['preco'] ? [] : ['valor_documento' => ['bail', 'required', 'numeric', 'decimal:0,2', 'gte:0']];

        $validador = Validator::make(
            $campos,
            [...RegrasProduto::regras(), ...$regraDoValor],
            [...RegrasProduto::mensagens(), 'valor_documento.decimal' => 'O valor do item deve ter no máximo 2 casas decimais.'],
            ['valor_documento' => 'valor do item'],
        );

        if ($validador->fails()) {
            $erros = [];
            $codigo = $campos['ncm'] ?? $campos['nbs'];
            $semClassificacao = $campos['cst'] === null && $campos['c_class_trib'] === null && $codigo !== null;

            foreach ($validador->errors()->messages() as $campo => $mensagens) {
                if ($semClassificacao && in_array($campo, ['cst', 'c_class_trib'], true)) {
                    continue;
                }

                foreach ($mensagens as $mensagem) {
                    $erros[] = ['campo' => $campo, 'mensagem' => $mensagem];
                }
            }

            if ($semClassificacao) {
                $rotulo = $campos['ncm'] !== null ? 'NCM' : 'NBS';
                $erros[] = [
                    'campo'    => 'cst',
                    'mensagem' => "Sem CST e cClassTrib (a nota não traz o grupo IBS/CBS) e nenhum produto do catálogo com o {$rotulo} {$codigo} para usar como referência. Cadastre um produto com esse {$rotulo} e importe de novo.",
                ];
            }

            return $erros;
        }

        $erros = [];

        foreach ([TipoNomenclatura::Ncm, TipoNomenclatura::Nbs] as $tipo) {
            $codigo = $campos[$tipo->value];

            if ($codigo !== null && ! $referencias->existeNomenclatura($tipo, $codigo)) {
                $erros[] = ['campo' => $tipo->value, 'mensagem' => "{$tipo->label()} {$codigo} não existe na tabela vigente."];
            }
        }

        $erroClassificacao = $referencias->erroDeClassificacao((string) $campos['cst'], (string) $campos['c_class_trib']);

        if ($erroClassificacao !== null) {
            $erros[] = $erroClassificacao;
        }

        return $erros;
    }

    /**
     * Planilhas perdem zeros à esquerda (CST 000 vira 0; NCM 01012100 vira 1012100).
     *
     * @param  array<string, string|null>  $campos
     * @return array{descricao: string|null, ncm: string|null, nbs: string|null, cst: string|null, c_class_trib: string|null, preco: string|null, valor_documento: string|null}
     */
    private static function normalizar(array $campos): array
    {
        $ncm = isset($campos['ncm']) ? NormalizadorFiscal::digitos($campos['ncm']) : null;
        $preco = isset($campos['preco']) ? NormalizadorFiscal::decimal($campos['preco']) : null;

        return [
            'descricao'    => isset($campos['descricao']) ? mb_trim($campos['descricao']) : null,
            'ncm'          => $ncm === null || $ncm === '' ? null : (mb_strlen($ncm) === 7 ? '0'.$ncm : $ncm),
            'nbs'          => isset($campos['nbs']) ? (NormalizadorFiscal::digitos($campos['nbs']) ?: null) : null,
            'cst'          => isset($campos['cst']) ? NormalizadorFiscal::codigo($campos['cst'], 3) : null,
            'c_class_trib' => isset($campos['c_class_trib']) ? NormalizadorFiscal::codigo($campos['c_class_trib'], 6) : null,
            'preco'        => $preco,
            // Valor da linha no documento (na planilha, o próprio preço).
            'valor_documento' => isset($campos['valor_documento']) ? NormalizadorFiscal::decimal($campos['valor_documento']) : $preco,
        ];
    }

    private function apagarArquivo(Importacao $importacao): void
    {
        if ($importacao->caminho_arquivo !== null) {
            Storage::disk('local')->delete($importacao->caminho_arquivo);
            $importacao->update(['caminho_arquivo' => null]);
        }
    }
}
