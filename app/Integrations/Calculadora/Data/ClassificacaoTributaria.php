<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Classificação tributária (cClassTrib) de CBS/IBS, vinda dos dados abertos.
 */
final readonly class ClassificacaoTributaria
{
    use LeCamposResposta;

    public function __construct(
        public string $codigo,
        public string $descricao,
        public string $tratamentoTributario,
        public ?string $nomenclatura = null,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            codigo: self::texto($dados, 'codigo'),
            descricao: self::texto($dados, 'descricao'),
            tratamentoTributario: self::texto($dados, 'descricaoTratamentoTributario'),
            nomenclatura: self::textoOpcional($dados, 'nomenclatura'),
        );
    }
}
