<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Alíquotas nominais (%) informadas à calculadora. Obrigatórias a partir de 2027.
 */
final readonly class AliquotasNominais
{
    public function __construct(
        public string $cbs,
        public string $ibsEstadual,
        public string $ibsMunicipal,
    ) {
        Decimal::validar($cbs, 'cbs');
        Decimal::validar($ibsEstadual, 'ibsEstadual');
        Decimal::validar($ibsMunicipal, 'ibsMunicipal');
    }

    /**
     * @return array{cbs: string, ibsEstadual: string, ibsMunicipal: string}
     */
    public function toPayload(): array
    {
        return [
            'cbs'          => $this->cbs,
            'ibsEstadual'  => $this->ibsEstadual,
            'ibsMunicipal' => $this->ibsMunicipal,
        ];
    }
}
