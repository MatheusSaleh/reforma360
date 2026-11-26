<?php

declare(strict_types=1);

namespace App\Services\Importacao;

use App\Enums\StatusImportacao;
use App\Enums\TipoNomenclatura;
use App\Importers\CatalogImporterResolver;
use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
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
 * vazio, linhas demais) desfazem tudo e marcam a importação como falha.
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
        $caminho = (string) $importacao->caminho_arquivo;

        try {
            DB::transaction(function () use ($importacao, $referencias, $caminho): void {
                $this->importadores->para($importacao->formato)->ler(
                    $caminho,
                    fn (array $lote) => $this->processarLote($importacao, $lote, $referencias),
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
        $importacao->update([
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
    private function processarLote(Importacao $importacao, array $lote, ReferenciasFiscais $referencias): void
    {
        $erros = [];
        $importadas = 0;
        $comErro = 0;

        foreach ($lote as $linha) {
            $campos = self::normalizar($linha->campos);
            $errosDaLinha = $this->validar($campos, $referencias);

            if ($errosDaLinha !== []) {
                $comErro++;

                foreach ($errosDaLinha as $erro) {
                    $erros[] = ['importacao_id' => $importacao->id, 'linha' => $linha->linha] + $erro;
                }

                continue;
            }

            $importacao->empresa->produtos()->updateOrCreate(['descricao' => $campos['descricao']], $campos);
            $importadas++;
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
     * @param  array{descricao: string|null, ncm: string|null, nbs: string|null, cst: string|null, c_class_trib: string|null, preco: string|null}  $campos
     * @return list<array{campo: string|null, mensagem: string}>
     */
    private function validar(array $campos, ReferenciasFiscais $referencias): array
    {
        $validador = Validator::make($campos, RegrasProduto::regras(), RegrasProduto::mensagens());

        if ($validador->fails()) {
            $erros = [];

            foreach ($validador->errors()->messages() as $campo => $mensagens) {
                foreach ($mensagens as $mensagem) {
                    $erros[] = ['campo' => $campo, 'mensagem' => $mensagem];
                }
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
     * @return array{descricao: string|null, ncm: string|null, nbs: string|null, cst: string|null, c_class_trib: string|null, preco: string|null}
     */
    private static function normalizar(array $campos): array
    {
        $ncm = isset($campos['ncm']) ? NormalizadorFiscal::digitos($campos['ncm']) : null;

        return [
            'descricao'    => isset($campos['descricao']) ? mb_trim($campos['descricao']) : null,
            'ncm'          => $ncm === null || $ncm === '' ? null : (mb_strlen($ncm) === 7 ? '0'.$ncm : $ncm),
            'nbs'          => isset($campos['nbs']) ? (NormalizadorFiscal::digitos($campos['nbs']) ?: null) : null,
            'cst'          => isset($campos['cst']) ? NormalizadorFiscal::codigo($campos['cst'], 3) : null,
            'c_class_trib' => isset($campos['c_class_trib']) ? NormalizadorFiscal::codigo($campos['c_class_trib'], 6) : null,
            'preco'        => isset($campos['preco']) ? NormalizadorFiscal::decimal($campos['preco']) : null,
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
