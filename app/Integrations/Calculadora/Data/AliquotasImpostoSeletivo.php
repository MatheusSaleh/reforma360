<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use InvalidArgumentException;

/**
 * Alíquotas do Imposto Seletivo informadas à calculadora (`aliquotasNominais.impostoSeletivo`).
 * Ainda não definidas em lei: são estimativas do usuário. Ad valorem em %; ad rem em R$ por unidade.
 */
final readonly class AliquotasImpostoSeletivo
{
    public function __construct(
        public ?string $adValorem = null,
        public ?string $adRem = null,
    ) {
        if ($adValorem === null && $adRem === null) {
            throw new InvalidArgumentException('Informe a alíquota ad valorem e/ou ad rem do Imposto Seletivo.');
        }

        Decimal::validar($adValorem, 'impostoSeletivo.adValorem');
        Decimal::validar($adRem, 'impostoSeletivo.adRem');
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return array_filter([
            'adValorem' => $this->adValorem,
            'adRem'     => $this->adRem,
        ], fn (?string $valor): bool => $valor !== null);
    }
}
