<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Simples Nacional × regime regular de IBS/CBS para os itens de uma simulação.
 *
 * @property numeric-string $aliquota_cbs_simples
 * @property numeric-string $aliquota_ibs_simples
 * @property numeric-string|null $compras
 * @property numeric-string $base_calculo
 * @property numeric-string $simples_cbs
 * @property numeric-string $simples_ibs
 * @property numeric-string $regular_cbs
 * @property numeric-string $regular_ibs
 * @property numeric-string $valor_imposto_seletivo
 * @property numeric-string $credito_estimado
 */
#[Table('comparacoes_regime')]
#[Fillable([
    'user_id', 'simulacao_id', 'atividade', 'aliquota_cbs_simples', 'aliquota_ibs_simples', 'compras', 'itens_comparados',
    'itens_nao_comparados', 'base_calculo', 'simples_cbs', 'simples_ibs', 'regular_cbs', 'regular_ibs', 'valor_imposto_seletivo', 'credito_estimado',
])]
class ComparacaoRegime extends Model
{
    protected $attributes = ['valor_imposto_seletivo' => '0.00'];

    /**
     * @return BelongsTo<Simulacao, $this>
     */
    public function simulacao(): BelongsTo
    {
        return $this->belongsTo(Simulacao::class);
    }

    /**
     * @return HasMany<ComparacaoItem, $this>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(ComparacaoItem::class, 'comparacao_id');
    }

    /**
     * @return numeric-string
     */
    public function totalSimples(): string
    {
        return bcadd(bcadd($this->simples_cbs, $this->simples_ibs, 2), $this->valor_imposto_seletivo, 2);
    }

    /**
     * @return numeric-string
     */
    public function debitosRegular(): string
    {
        return bcadd($this->debitosCbsIbs(), $this->valor_imposto_seletivo, 2);
    }

    /**
     * Débitos menos o crédito estimado das compras (nunca negativo).
     *
     * @return numeric-string
     */
    public function liquidoRegular(): string
    {
        $liquido = bcsub($this->debitosCbsIbs(), $this->credito_estimado, 2);

        return bcadd(bccomp($liquido, '0', 2) < 0 ? '0.00' : $liquido, $this->valor_imposto_seletivo, 2);
    }

    public function sobraCredito(): bool
    {
        return bccomp($this->credito_estimado, $this->debitosCbsIbs(), 2) > 0;
    }

    /**
     * Regular líquido − Simples: positivo = Simples custa menos.
     *
     * @return numeric-string
     */
    public function diferenca(): string
    {
        return bcsub($this->liquidoRegular(), $this->totalSimples(), 2);
    }

    /** @return numeric-string */
    private function debitosCbsIbs(): string
    {
        return bcadd($this->regular_cbs, $this->regular_ibs, 2);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aliquota_cbs_simples'   => 'decimal:4',
            'aliquota_ibs_simples'   => 'decimal:4',
            'compras'                => 'decimal:2',
            'base_calculo'           => 'decimal:2',
            'simples_cbs'            => 'decimal:2',
            'simples_ibs'            => 'decimal:2',
            'regular_cbs'            => 'decimal:2',
            'regular_ibs'            => 'decimal:2',
            'valor_imposto_seletivo' => 'decimal:2',
            'credito_estimado'       => 'decimal:2',
        ];
    }
}
