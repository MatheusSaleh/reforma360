<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Totais da operação. Somam apenas os itens calculados.
 */
final readonly class TotalOperacao
{
    use LeCamposResposta;

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
            baseCalculoIbsCbs: self::texto($ibsCbs, 'vBCIBSCBS'),
            valorCbs: self::texto(self::grupo($ibsCbs, 'gCBS'), 'vCBS'),
            valorIbs: self::texto($ibs, 'vIBS'),
            valorIbsEstadual: self::texto(self::grupo($ibs, 'gIBSUF'), 'vIBSUF'),
            valorIbsMunicipal: self::texto(self::grupo($ibs, 'gIBSMun'), 'vIBSMun'),
            valorImpostoSeletivo: self::textoOpcional($impostoSeletivo, 'vIS'),
        );
    }
}
