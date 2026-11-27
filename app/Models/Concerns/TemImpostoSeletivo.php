<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Services\Simulacao\Data\DadosImpostoSeletivo;

/**
 * Colunas `is_*` do Imposto Seletivo (produto e retrato do produto no item simulado).
 *
 * @property string|null $is_cst
 * @property string|null $is_c_class_trib
 * @property string|null $is_unidade
 * @property numeric-string|null $is_quantidade
 * @property numeric-string|null $is_aliquota_ad_valorem
 * @property numeric-string|null $is_aliquota_ad_rem
 */
trait TemImpostoSeletivo
{
    public const array CAMPOS_IMPOSTO_SELETIVO = DadosImpostoSeletivo::CAMPOS;

    public function impostoSeletivo(): ?DadosImpostoSeletivo
    {
        return DadosImpostoSeletivo::deCampos($this->only(self::CAMPOS_IMPOSTO_SELETIVO));
    }

    /**
     * @return array<string, string>
     */
    protected static function castsDoImpostoSeletivo(): array
    {
        return [
            'is_quantidade'          => 'decimal:4',
            'is_aliquota_ad_valorem' => 'decimal:4',
            'is_aliquota_ad_rem'     => 'decimal:4',
        ];
    }
}
