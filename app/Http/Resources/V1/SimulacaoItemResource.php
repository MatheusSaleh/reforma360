<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\SimulacaoItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resultado de um item da simulação do catálogo.
 *
 * @mixin SimulacaoItem
 */
class SimulacaoItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'produto_id'             => $this->produto_id,
            'descricao'              => $this->descricao,
            'ncm'                    => $this->ncm,
            'nbs'                    => $this->nbs,
            'cst'                    => $this->cst,
            'c_class_trib'           => $this->c_class_trib,
            'base_calculo'           => $this->base_calculo,
            'estado'                 => $this->estado?->value,
            'calculado'              => $this->estado?->foiCalculado() ?? false,
            'percentual_reducao'     => $this->percentual_reducao,
            'valor_cbs'              => $this->valor_cbs,
            'valor_ibs'              => $this->valor_ibs,
            'valor_imposto_seletivo' => $this->valor_imposto_seletivo,
            'total_tributos'         => $this->total_tributos,
            'erros'                  => $this->erros ?? [],
        ];
    }
}
