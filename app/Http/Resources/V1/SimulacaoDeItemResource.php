<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Integrations\Calculadora\Data\ParcelaTributo;
use App\Services\Simulacao\Data\SimulacaoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resultado da simulação síncrona de um item.
 *
 * @property SimulacaoItem $resource
 */
class SimulacaoDeItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $simulacao = $this->resource;
        $ibsCbs = $simulacao->ibsCbs;
        $parcela = fn (ParcelaTributo $p): array => [
            'aliquota'           => $p->aliquota,
            'percentual_reducao' => $p->percentualReducao,
            'aliquota_efetiva'   => $p->aliquotaEfetiva ?? $p->aliquota,
            'valor'              => $p->valor,
        ];

        return [
            'calculo_simulado'  => $simulacao->usouAliquotasInformadas,
            'data_fato_gerador' => $simulacao->entrada->data->toDateString(),
            'cst'               => $ibsCbs->cst,
            'c_class_trib'      => $ibsCbs->cClassTrib,
            'base_calculo'      => $ibsCbs->baseCalculo,
            'cbs'               => $parcela($ibsCbs->cbs),
            'ibs_estadual'      => $parcela($ibsCbs->ibsEstadual),
            'ibs_municipal'     => $parcela($ibsCbs->ibsMunicipal),
            'valor_ibs'         => $ibsCbs->valorIbs,
            'imposto_seletivo'  => $simulacao->impostoSeletivo === null ? null : [
                'aliquota'        => $simulacao->impostoSeletivo->aliquota,
                'valor'           => $simulacao->impostoSeletivo->valor,
                'aliquota_ad_rem' => $simulacao->impostoSeletivo->aliquotaAdRem,
                'base_calculo'    => $simulacao->impostoSeletivo->baseCalculo,
                'quantidade'      => $simulacao->impostoSeletivo->quantidade,
                'unidade'         => $simulacao->impostoSeletivo->unidade,
            ],
            'total_tributos' => $simulacao->totalTributos,
            'classificacao'  => $simulacao->classificacao === null ? null : [
                'codigo'     => $simulacao->classificacao->codigo,
                'descricao'  => $simulacao->classificacao->descricao,
                'tratamento' => $simulacao->classificacao->tratamentoTributario,
            ],
            'fundamentacao_legal' => $simulacao->fundamentacao === null ? null : [
                'referencia' => $simulacao->fundamentacao->textoCurto,
                'texto'      => $simulacao->fundamentacao->texto,
            ],
            'memoria_calculo' => array_filter([
                'cbs'              => $ibsCbs->memoriaCbs,
                'ibs_estadual'     => $ibsCbs->memoriaIbsEstadual,
                'ibs_municipal'    => $ibsCbs->memoriaIbsMunicipal,
                'imposto_seletivo' => $simulacao->impostoSeletivo?->memoriaCalculo,
            ]),
            'versao_base_dados' => $simulacao->versaoBaseDados,
            'aviso'             => AvisoDeSimulacao::TEXTO,
        ];
    }
}
