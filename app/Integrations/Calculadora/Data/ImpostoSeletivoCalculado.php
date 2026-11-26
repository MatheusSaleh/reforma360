<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Resultado do Imposto Seletivo de um item (grupo "IS" do tribCalc).
 */
final readonly class ImpostoSeletivoCalculado
{
    use LeCamposResposta;

    public function __construct(
        public string $cst,
        public string $cClassTrib,
        public string $baseCalculo,
        public string $aliquota,
        public string $valor,
        public ?string $memoriaCalculo = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            cst: self::texto($dados, 'CSTIS'),
            cClassTrib: self::texto($dados, 'cClassTribIS'),
            baseCalculo: self::texto($dados, 'vBCIS'),
            aliquota: self::texto($dados, 'pIS'),
            valor: self::texto($dados, 'vIS'),
            memoriaCalculo: self::textoOpcional($dados, 'memoriaCalculo'),
        );
    }
}
