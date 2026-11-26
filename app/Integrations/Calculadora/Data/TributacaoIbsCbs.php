<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Resultado de CBS e IBS de um item (grupo "IBSCBS" do tribCalc).
 */
final readonly class TributacaoIbsCbs
{
    use LeCamposResposta;

    public function __construct(
        public string $cst,
        public string $cClassTrib,
        public string $baseCalculo,
        public ParcelaTributo $cbs,
        public ParcelaTributo $ibsEstadual,
        public ParcelaTributo $ibsMunicipal,
        public string $valorIbs,
        public ?string $memoriaCbs = null,
        public ?string $memoriaIbsEstadual = null,
        public ?string $memoriaIbsMunicipal = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        $grupo = self::grupo($dados, 'gIBSCBS');
        $memoria = self::grupoOpcional($dados, 'memoriaCalculo') ?? [];

        return new self(
            cst: self::texto($dados, 'CST'),
            cClassTrib: self::texto($dados, 'cClassTrib'),
            baseCalculo: self::texto($grupo, 'vBC'),
            cbs: ParcelaTributo::fromGrupo(self::grupo($grupo, 'gCBS'), 'pCBS', 'vCBS'),
            ibsEstadual: ParcelaTributo::fromGrupo(self::grupo($grupo, 'gIBSUF'), 'pIBSUF', 'vIBSUF'),
            ibsMunicipal: ParcelaTributo::fromGrupo(self::grupo($grupo, 'gIBSMun'), 'pIBSMun', 'vIBSMun'),
            valorIbs: self::texto($grupo, 'vIBS'),
            memoriaCbs: self::textoOpcional($memoria, 'cbs'),
            memoriaIbsEstadual: self::textoOpcional($memoria, 'ibsUF'),
            memoriaIbsMunicipal: self::textoOpcional($memoria, 'ibsMun'),
        );
    }
}
