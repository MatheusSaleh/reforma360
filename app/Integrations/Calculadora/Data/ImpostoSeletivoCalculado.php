<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Resultado do Imposto Seletivo de um item (grupo "IS" do tribCalc).
 */
final readonly class ImpostoSeletivoCalculado
{
    use LeCamposResposta;

    /**
     * @param  numeric-string  $baseCalculo
     * @param  numeric-string|null  $aliquota  ad valorem (%); ausente quando só há alíquota ad rem
     * @param  numeric-string  $valor
     * @param  numeric-string|null  $aliquotaAdRem  R$ por unidade (`unidade`)
     * @param  numeric-string|null  $quantidade
     */
    public function __construct(
        public string $cst,
        public string $cClassTrib,
        public string $baseCalculo,
        public ?string $aliquota,
        public string $valor,
        public ?string $memoriaCalculo = null,
        public ?string $aliquotaAdRem = null,
        public ?string $quantidade = null,
        public ?string $unidade = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            cst: self::texto($dados, 'CSTIS'),
            cClassTrib: self::texto($dados, 'cClassTribIS'),
            baseCalculo: self::decimal($dados, 'vBCIS'),
            aliquota: self::decimalOpcional($dados, 'pIS'),
            valor: self::decimal($dados, 'vIS'),
            memoriaCalculo: self::textoOpcional($dados, 'memoriaCalculo'),
            aliquotaAdRem: self::decimalOpcional($dados, 'adRemIS') ?? self::decimalOpcional($dados, 'pISEspec'),
            quantidade: self::decimalOpcional($dados, 'qTrib'),
            unidade: self::textoOpcional($dados, 'uTrib'),
        );
    }
}
