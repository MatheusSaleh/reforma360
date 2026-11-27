<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use InvalidArgumentException;

/**
 * Grupo `impostoSeletivo` de um item. A base do IS é o valor do item sem o IS; a calculadora
 * calcula o IS e deriva a base de CBS/IBS (base do IS + IS), por isso o item vai sem `baseCalculo`.
 */
final readonly class ImpostoSeletivoOperacao
{
    public function __construct(
        public string $cst,
        public string $cClassTrib,
        public string $baseCalculo,
        public AliquotasImpostoSeletivo $aliquotas,
        public ?string $quantidade = null,
        public ?string $unidade = null,
    ) {
        Decimal::validar($baseCalculo, 'impostoSeletivo.baseCalculo');
        Decimal::validar($quantidade, 'impostoSeletivo.quantidade');

        if ($aliquotas->adRem !== null && ($quantidade === null || $unidade === null)) {
            throw new InvalidArgumentException('Quantidade e unidade do Imposto Seletivo são obrigatórias com alíquota ad rem.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return array_filter([
            'cst'         => $this->cst,
            'cClassTrib'  => $this->cClassTrib,
            'baseCalculo' => $this->baseCalculo,
            'quantidade'  => $this->quantidade,
            'unidade'     => $this->unidade,
        ], fn (?string $valor): bool => $valor !== null);
    }
}
