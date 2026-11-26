<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use InvalidArgumentException;

/**
 * Item de uma operação enviada à calculadora (mercadoria com NCM ou serviço com NBS).
 */
final readonly class ItemOperacao
{
    public function __construct(
        public int $numero,
        public string $cst,
        public string $cClassTrib,
        public ?string $baseCalculo = null,
        public ?string $ncm = null,
        public ?string $nbs = null,
        public ?string $quantidade = null,
        public ?string $unidade = null,
        public ?AliquotasNominais $aliquotasNominais = null,
    ) {
        if ($ncm !== null && $nbs !== null) {
            throw new InvalidArgumentException("O item {$numero} deve ter NCM ou NBS, não os dois.");
        }

        Decimal::validar($baseCalculo, 'baseCalculo');
        Decimal::validar($quantidade, 'quantidade');
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return array_filter([
            'numero'            => $this->numero,
            'ncm'               => $this->ncm,
            'nbs'               => $this->nbs,
            'cst'               => $this->cst,
            'cClassTrib'        => $this->cClassTrib,
            'baseCalculo'       => $this->baseCalculo,
            'quantidade'        => $this->quantidade,
            'unidade'           => $this->unidade,
            'aliquotasNominais' => $this->aliquotasNominais?->toPayload(),
        ], fn (mixed $valor): bool => $valor !== null);
    }
}
