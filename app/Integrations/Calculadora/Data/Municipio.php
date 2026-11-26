<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

final readonly class Municipio
{
    use LeCamposResposta;

    public function __construct(
        public int $codigo,
        public string $nome,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            codigo: self::inteiro($dados, 'codigo'),
            nome: self::texto($dados, 'nome'),
        );
    }
}
