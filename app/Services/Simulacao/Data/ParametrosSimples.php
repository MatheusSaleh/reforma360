<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

/**
 * Dados do contribuinte no Simples Nacional informados para a comparação.
 */
final readonly class ParametrosSimples
{
    public function __construct(
        public string $atividade,
        public string $aliquotaCbs,
        public string $aliquotaIbs,
        public ?string $compras = null,
    ) {}
}
