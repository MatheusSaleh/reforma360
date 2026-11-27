<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Linha importada com sucesso: o produto gravado e o valor da linha (na nota fiscal,
 * o valor do item, usado como base de cálculo ao simular a nota).
 *
 * @property numeric-string $valor
 * @property-read Produto $produto
 */
#[Table('importacao_produtos', timestamps: false)]
#[Fillable(['produto_id', 'linha', 'valor'])]
class ImportacaoProduto extends Model
{
    /**
     * @return BelongsTo<Importacao, $this>
     */
    public function importacao(): BelongsTo
    {
        return $this->belongsTo(Importacao::class);
    }

    /**
     * @return BelongsTo<Produto, $this>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['valor' => 'decimal:2'];
    }
}
