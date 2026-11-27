<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Enums\StatusImportacao;
use App\Enums\StatusSimulacao;
use App\Models\Importacao;
use App\Models\ImportacaoProduto;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\User;
use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use App\Services\Simulacao\Data\ParametrosSimulacao;
use App\Services\Simulacao\SimularItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cria a simulação com um retrato do catálogo (ou só dos itens de uma nota importada,
 * com o valor de cada item na nota como base de cálculo) e despacha o lote de jobs
 * (um job por bloco de itens = uma chamada à calculadora).
 */
final readonly class IniciarSimulacaoEmLote
{
    public function __construct(
        private SimularItemService $simulacaoDeItem,
        private DespacharLoteDaSimulacao $despacharLote,
    ) {}

    /**
     * @throws ValidationException quando não há itens para simular
     */
    public function handle(User $usuario, ParametrosSimulacao $parametros, ?Importacao $importacao = null): Simulacao
    {
        $empresa = $usuario->empresa;

        if ($importacao !== null && ($importacao->status !== StatusImportacao::Concluida || ! $importacao->itens()->exists())) {
            throw ValidationException::withMessages(['simulacao' => 'Esta importação não tem itens importados para simular.']);
        }

        if ($importacao === null && ! $empresa->produtos()->exists()) {
            throw ValidationException::withMessages(['simulacao' => 'Cadastre ou importe produtos antes de simular o catálogo.']);
        }

        $aliquotas = $this->simulacaoDeItem->exigeAliquotasNominais($parametros->data->year) ? $parametros->aliquotasNominais : null;

        $simulacao = DB::transaction(function () use ($empresa, $usuario, $parametros, $aliquotas, $importacao): Simulacao {
            $simulacao = $empresa->simulacoes()->create([
                'user_id'                => $usuario->id,
                'importacao_id'          => $importacao?->id,
                'status'                 => StatusSimulacao::Pendente,
                'data_fato_gerador'      => $parametros->data->toDateString(),
                'uf'                     => $parametros->uf,
                'municipio'              => $parametros->municipio,
                'aliquota_cbs'           => $aliquotas?->cbs,
                'aliquota_ibs_estadual'  => $aliquotas?->ibsEstadual,
                'aliquota_ibs_municipal' => $aliquotas?->ibsMunicipal,
            ]);

            if ($importacao !== null) {
                $importacao->itens()->with('produto')->orderBy('id')->chunkById(500, function ($itens) use ($simulacao): void {
                    $simulacao->itens()->insert($itens->map(
                        fn (ImportacaoProduto $item): array => self::itemDaSimulacao($simulacao, $item->produto, $item->valor, DadosImpostoSeletivo::deCampos($item->dados_imposto_seletivo ?? [])),
                    )->all());
                });
            } else {
                $empresa->produtos()->orderBy('id')->chunkById(500, function ($produtos) use ($simulacao): void {
                    $simulacao->itens()->insert($produtos->map(
                        fn (Produto $produto): array => self::itemDaSimulacao($simulacao, $produto, $produto->preco),
                    )->all());
                });
            }

            $simulacao->update(['total_itens' => $simulacao->itens()->count()]);

            return $simulacao;
        });

        $this->despacharLote->handle($simulacao);

        return $simulacao;
    }

    /**
     * @return array<string, mixed>
     */
    private static function itemDaSimulacao(Simulacao $simulacao, Produto $produto, string $baseCalculo, ?DadosImpostoSeletivo $impostoSeletivo = null): array
    {
        // Na nota, prioriza o grupo IS da própria linha. No catálogo, usa os dados por unidade vendida.
        $camposIs = ($impostoSeletivo ?? $produto->impostoSeletivo())?->campos()
            ?? array_fill_keys(Produto::CAMPOS_IMPOSTO_SELETIVO, null);

        return [
            'simulacao_id' => $simulacao->id,
            'produto_id'   => $produto->id,
            'descricao'    => $produto->descricao,
            'ncm'          => $produto->ncm,
            'nbs'          => $produto->nbs,
            'cst'          => $produto->cst,
            'c_class_trib' => $produto->c_class_trib,
            'base_calculo' => $baseCalculo,
            ...$camposIs,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
