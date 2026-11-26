<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Enums\Uf;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use Carbon\CarbonImmutable;

/**
 * Parâmetros da operação aplicados a todos os itens de uma simulação em lote.
 */
final readonly class ParametrosSimulacao
{
    public function __construct(
        public CarbonImmutable $data,
        public Uf $uf,
        public int $municipio,
        public ?AliquotasNominais $aliquotasNominais = null,
    ) {}
}
