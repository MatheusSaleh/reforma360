<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Totais da operação. Somam apenas os itens calculados.
 */
final readonly class TotalOperacao
{
    use LeCamposResposta;

    /**
     * @param  numeric-string  $baseCalculoIbsCbs
     * @param  numeric-string  $valorCbs
     * @param  numeric-string  $valorIbs
     * @param  numeric-string  $valorIbsEstadual
     * @param  numeric-string  $valorIbsMunicipal
     * @param  numeric-string|null  $valorImpostoSeletivo
     */
    public function __construct(
        public string $baseCalculoIbsCbs,
        public string $valorCbs,
        public string $valorIbs,
        public string $valorIbsEstadual,
        public string $valorIbsMunicipal,
        public ?string $valorImpostoSeletivo = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados  bloco "total" da resposta
     */
    public static function fromArray(array $dados): self
    {
        $tributos = self::grupo($dados, 'tribCalc');
        $ibsCbs = self::grupo($tributos, 'IBSCBSTot');
        $ibs = self::grupo($ibsCbs, 'gIBS');
        $impostoSeletivo = self::grupoOpcional($tributos, 'ISTot') ?? [];

        return new self(
            baseCalculoIbsCbs: self::decimal($ibsCbs, 'vBCIBSCBS'),
            valorCbs: self::decimal(self::grupo($ibsCbs, 'gCBS'), 'vCBS'),
            valorIbs: self::decimal($ibs, 'vIBS'),
            valorIbsEstadual: self::decimal(self::grupo($ibs, 'gIBSUF'), 'vIBSUF'),
            valorIbsMunicipal: self::decimal(self::grupo($ibs, 'gIBSMun'), 'vIBSMun'),
            valorImpostoSeletivo: self::decimalOpcional($impostoSeletivo, 'vIS'),
        );
    }
}
