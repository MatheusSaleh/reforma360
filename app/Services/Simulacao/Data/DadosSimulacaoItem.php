<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Enums\Uf;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use Carbon\CarbonImmutable;

/**
 * Entrada validada da simulação de um item.
 */
final readonly class DadosSimulacaoItem
{
    public function __construct(
        public string $cst,
        public string $cClassTrib,
        public string $valor,
        public Uf $uf,
        public int $municipio,
        public CarbonImmutable $data,
        public ?string $ncm = null,
        public ?string $nbs = null,
        public ?AliquotasNominais $aliquotasNominais = null,
    ) {}
}
