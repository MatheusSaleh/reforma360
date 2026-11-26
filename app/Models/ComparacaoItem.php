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
 * @property list<array{codigo: string|null, mensagem: string}>|null $erros
 */
#[Table('comparacao_itens', timestamps: false)]
#[Fillable(['simulacao_item_id', 'estado', 'simples_cbs', 'simples_ibs', 'erros'])]
class ComparacaoItem extends Model
{
    /**
     * @return BelongsTo<SimulacaoItem, $this>
     */
    public function simulacaoItem(): BelongsTo
    {
        return $this->belongsTo(SimulacaoItem::class);
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
        return $this->simples_cbs === null || $this->simples_ibs === null ? null : bcadd($this->simples_cbs, $this->simples_ibs, 2);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado'      => EstadoItemCalculo::class,
            'simples_cbs' => 'decimal:2',
            'simples_ibs' => 'decimal:2',
            'erros'       => 'array',
        ];
    }
}
