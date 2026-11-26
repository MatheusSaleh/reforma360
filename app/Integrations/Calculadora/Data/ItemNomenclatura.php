<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Código NCM ou NBS com a descrição do último nível.
 */
final readonly class ItemNomenclatura
{
    use LeCamposResposta;

    public function __construct(
        public string $codigo,
        public string $descricao,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados, string $campoCodigo): self
    {
        return new self(
            codigo: self::texto($dados, $campoCodigo),
            descricao: mb_ltrim(self::texto($dados, 'descricao'), '- '),
        );
    }
}
