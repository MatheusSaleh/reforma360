<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoItemCalculo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property EstadoItemCalculo $estado
 * @property numeric-string|null $simples_cbs
 * @property numeric-string|null $simples_ibs
 * @property numeric-string|null $valor_imposto_seletivo
 * @property list<array{codigo: string|null, mensagem: string}>|null $erros
 */
#[Table('comparacao_itens', timestamps: false)]
#[Fillable(['simulacao_item_id', 'estado', 'simples_cbs', 'simples_ibs', 'valor_imposto_seletivo', 'erros'])]
class ComparacaoItem extends Model
{
    /**
     * @return BelongsTo<SimulacaoItem, $this>
     */
    public function simulacaoItem(): BelongsTo
    {
        return $this->belongsTo(SimulacaoItem::class);
    }

    /** @return numeric-string */
    public function regularBruto(): string
    {
        $item = $this->simulacaoItem;
        $impostoSeletivo = $this->foiComparado() ? $this->valor_imposto_seletivo : $item?->valor_imposto_seletivo;

        return bcadd(bcadd($item->valor_cbs ?? '0', $item->valor_ibs ?? '0', 2), $impostoSeletivo ?? '0', 2);
    }

    public function foiComparado(): bool
    {
        return $this->estado->foiCalculado() && $this->simples_cbs !== null;
    }

    /**
     * @return numeric-string|null
     */
    public function totalSimples(): ?string
    {
        if ($this->simples_cbs === null || $this->simples_ibs === null) {
            return null;
        }

        return bcadd(bcadd($this->simples_cbs, $this->simples_ibs, 2), $this->valor_imposto_seletivo ?? '0', 2);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado'                 => EstadoItemCalculo::class,
            'simples_cbs'            => 'decimal:2',
            'simples_ibs'            => 'decimal:2',
            'valor_imposto_seletivo' => 'decimal:2',
            'erros'                  => 'array',
        ];
    }
}
