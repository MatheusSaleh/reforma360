<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Erro apontado pela calculadora, por item (observabilidade) ou na operação inteira (HTTP 422).
 */
final readonly class ErroCalculo
{
    use LeCamposResposta;

    public function __construct(
        public string $codigo,
        public string $titulo,
        public ?string $campo,
        public string $detalhe,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            codigo: self::texto($dados, 'code'),
            titulo: self::texto($dados, 'title'),
            campo: self::textoOpcional($dados, 'pointer'),
            detalhe: self::texto($dados, 'detail'),
        );
    }
}
