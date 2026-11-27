<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Item enviado à calculadora do Simples Nacional, com as alíquotas efetivas do
 * contribuinte no Simples (pCBSSN/pIBSSN) e o código da atividade (cAtivSN).
 */
final readonly class ItemSimples
{
    public function __construct(
        public int $numero,
        public string $atividade,
        public string $cClassTrib,
        public string $baseCalculo,
        public string $aliquotaCbs,
        public string $aliquotaIbs,
        public ?string $ncm = null,
        public ?string $nbs = null,
        public string $valorImpostoSeletivo = '0.00',
    ) {
        Decimal::validar($baseCalculo, 'baseCalculo');
        Decimal::validar($aliquotaCbs, 'pCBSSN');
        Decimal::validar($aliquotaIbs, 'pIBSSN');
        Decimal::validar($valorImpostoSeletivo, 'valorImpostoSeletivo');
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return array_filter([
            'numero'               => $this->numero,
            'cAtivSN'              => $this->atividade,
            'cClassTrib'           => $this->cClassTrib,
            'ncm'                  => $this->ncm,
            'nbs'                  => $this->nbs,
            'baseCalculo'          => $this->baseCalculo,
            'valorImpostoSeletivo' => $this->valorImpostoSeletivo,
            'pIBSSN'               => $this->aliquotaIbs,
            'pCBSSN'               => $this->aliquotaCbs,
        ], fn (mixed $valor): bool => $valor !== null);
    }
}
