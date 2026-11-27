<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Produto;
use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Produto
 */
class ProdutoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'descricao'    => $this->descricao,
            'tipo'         => $this->tipo()->value,
            'ncm'          => $this->ncm,
            'nbs'          => $this->nbs,
            'cst'          => $this->cst,
            'c_class_trib' => $this->c_class_trib,
            'preco'        => $this->preco,
            ...($this->impostoSeletivo()?->campos() ?? array_fill_keys(DadosImpostoSeletivo::CAMPOS, null)),
            'criado_em'     => $this->created_at?->toIso8601String(),
            'atualizado_em' => $this->updated_at?->toIso8601String(),
        ];
    }
}
