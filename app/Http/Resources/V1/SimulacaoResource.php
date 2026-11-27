<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Simulacao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Simulação do catálogo (em lote). Os resultados por item ficam em /simulacoes/{id}/itens.
 *
 * @mixin Simulacao
 */
class SimulacaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $aliquotas = $this->aliquotasNominais();

        return [
            'id'                 => $this->id,
            'status'             => $this->status->value,
            'status_descricao'   => $this->status->label(),
            'em_andamento'       => $this->status->emAndamento(),
            'data_fato_gerador'  => $this->data_fato_gerador->toDateString(),
            'uf'                 => $this->uf->value,
            'municipio'          => $this->municipio,
            'aliquotas_nominais' => $aliquotas === null ? null : [
                'cbs'           => $aliquotas->cbs,
                'ibs_estadual'  => $aliquotas->ibsEstadual,
                'ibs_municipal' => $aliquotas->ibsMunicipal,
            ],
            'calculo_simulado' => $aliquotas !== null,
            'importacao_id'    => $this->importacao_id,
            'itens'            => [
                'total'       => $this->total_itens,
                'processados' => $this->itens_processados ?? 0,
                'com_erro'    => $this->itens_com_erro ?? 0,
            ],
            'totais' => [
                'base_calculo'     => $this->total_base_calculo,
                'cbs'              => $this->total_cbs,
                'ibs'              => $this->total_ibs,
                'imposto_seletivo' => $this->total_imposto_seletivo,
                'tributos'         => $this->total_tributos,
            ],
            'criada_em'    => $this->created_at?->toIso8601String(),
            'concluida_em' => $this->concluida_em?->toIso8601String(),
            'aviso'        => AvisoDeSimulacao::TEXTO,
        ];
    }
}
