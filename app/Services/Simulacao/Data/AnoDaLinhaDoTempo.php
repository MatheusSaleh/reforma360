<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Enums\StatusSimulacao;
use App\Integrations\Calculadora\Data\AliquotasNominais;

/**
 * Um ano da linha do tempo: alíquotas usadas e valores (do total ou de um item).
 * Valores nulos = o ano não tem resultado (em andamento ou com erro).
 */
final readonly class AnoDaLinhaDoTempo
{
    /**
     * @param  numeric-string|null  $base
     * @param  numeric-string|null  $cbs
     * @param  numeric-string|null  $ibs
     * @param  numeric-string|null  $impostoSeletivo
     * @param  numeric-string|null  $total
     * @param  numeric-string|null  $carga  CBS + IBS + IS sobre a base, em %
     */
    public function __construct(
        public int $ano,
        public int $simulacaoId,
        public StatusSimulacao $status,
        public ?AliquotasNominais $aliquotas,
        public ?string $base,
        public ?string $cbs,
        public ?string $ibs,
        public ?string $impostoSeletivo,
        public ?string $total,
        public ?string $carga,
        public int $itensComErro = 0,
        public ?string $erro = null,
    ) {}

    /**
     * IBS estadual + municipal nominal do ano, sem zeros finais (null em 2026).
     */
    public function aliquotaIbsTotal(): ?string
    {
        if ($this->aliquotas === null || ! is_numeric($this->aliquotas->ibsEstadual) || ! is_numeric($this->aliquotas->ibsMunicipal)) {
            return null;
        }

        $soma = bcadd($this->aliquotas->ibsEstadual, $this->aliquotas->ibsMunicipal, 4);

        return mb_rtrim(mb_rtrim($soma, '0'), '.') ?: '0';
    }
}
