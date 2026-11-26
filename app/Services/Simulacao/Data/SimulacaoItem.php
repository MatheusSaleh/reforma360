<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Integrations\Calculadora\Data\ClassificacaoTributaria;
use App\Integrations\Calculadora\Data\FundamentacaoLegal;
use App\Integrations\Calculadora\Data\ImpostoSeletivoCalculado;
use App\Integrations\Calculadora\Data\TributacaoIbsCbs;

/**
 * Resultado da simulação de um item, pronto para exibição.
 */
final readonly class SimulacaoItem
{
    public function __construct(
        public DadosSimulacaoItem $entrada,
        public TributacaoIbsCbs $ibsCbs,
        public string $totalTributos,
        public bool $usouAliquotasInformadas,
        public ?ImpostoSeletivoCalculado $impostoSeletivo = null,
        public ?ClassificacaoTributaria $classificacao = null,
        public ?FundamentacaoLegal $fundamentacao = null,
        public ?string $versaoBaseDados = null,
    ) {}
}
