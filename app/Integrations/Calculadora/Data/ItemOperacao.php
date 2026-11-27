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
        public ?ImpostoSeletivoOperacao $impostoSeletivo = null,
    ) {
        if ($ncm !== null && $nbs !== null) {
            throw new InvalidArgumentException("O item {$numero} deve ter NCM ou NBS, não os dois.");
        }

        if ($impostoSeletivo !== null && $aliquotasNominais === null) {
            throw new InvalidArgumentException("O item {$numero} tem Imposto Seletivo e precisa das alíquotas nominais.");
        }

        Decimal::validar($baseCalculo, 'baseCalculo');
        Decimal::validar($quantidade, 'quantidade');
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $aliquotas = $this->aliquotasNominais?->toPayload();

        if ($this->impostoSeletivo !== null && $aliquotas !== null) {
            $aliquotas['impostoSeletivo'] = $this->impostoSeletivo->aliquotas->toPayload();
        }

        return array_filter([
            'numero'     => $this->numero,
            'ncm'        => $this->ncm,
            'nbs'        => $this->nbs,
            'cst'        => $this->cst,
            'cClassTrib' => $this->cClassTrib,
            // Com IS, a calculadora deriva a base de CBS/IBS (base do IS + IS).
            'baseCalculo'       => $this->impostoSeletivo === null ? $this->baseCalculo : null,
            'quantidade'        => $this->quantidade,
            'unidade'           => $this->unidade,
            'impostoSeletivo'   => $this->impostoSeletivo?->toPayload(),
            'aliquotasNominais' => $aliquotas,
        ], fn (mixed $valor): bool => $valor !== null);
    }
}
