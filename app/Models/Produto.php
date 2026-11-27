<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoItem;
use App\Models\Concerns\TemImpostoSeletivo;
use Database\Factories\ProdutoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Produto ou serviço do catálogo de uma empresa.
 *
 * @property numeric-string $preco
 */
#[Fillable([
    'descricao', 'ncm', 'nbs', 'cst', 'c_class_trib', 'preco',
    'is_cst', 'is_c_class_trib', 'is_unidade', 'is_quantidade', 'is_aliquota_ad_valorem', 'is_aliquota_ad_rem',
])]
class Produto extends Model
{
    /** @use HasFactory<ProdutoFactory> */
    use HasFactory;

    use TemImpostoSeletivo;

    /**
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipo(): TipoItem
    {
        return $this->nbs !== null ? TipoItem::Servico : TipoItem::Mercadoria;
    }

    /**
     * Busca por descrição, NCM ou NBS.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function busca(Builder $query, ?string $termo): void
    {
        $termo = mb_trim((string) $termo);

        if ($termo === '') {
            return;
        }

        $digitos = preg_replace('/\D/', '', $termo);

        $query->where(function (Builder $query) use ($termo, $digitos): void {
            $query->where('descricao', 'like', '%'.$termo.'%');

            if ($digitos !== null && $digitos !== '') {
                $query->orWhere('ncm', 'like', $digitos.'%')->orWhere('nbs', 'like', $digitos.'%');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            ...self::castsDoImpostoSeletivo(),
        ];
    }
}
