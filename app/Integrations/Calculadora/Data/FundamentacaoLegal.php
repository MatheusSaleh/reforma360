<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Fundamentação legal de uma classificação tributária (dados abertos).
 */
final readonly class FundamentacaoLegal
{
    use LeCamposResposta;

    public function __construct(
        public string $cClassTrib,
        public string $situacaoTributaria,
        public string $textoCurto,
        public string $texto,
        public string $referenciaNormativa,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            cClassTrib: self::texto($dados, 'codigoClassificacaoTributaria'),
            situacaoTributaria: self::texto($dados, 'descricaoSituacaoTributaria'),
            textoCurto: self::texto($dados, 'textoCurto'),
            texto: self::quebrasDeLinha(self::texto($dados, 'texto')),
            referenciaNormativa: self::quebrasDeLinha(self::texto($dados, 'referenciaNormativa')),
        );
    }

    /**
     * A API devolve as quebras de linha escapadas ("\n" literal).
     */
    private static function quebrasDeLinha(string $texto): string
    {
        return mb_trim(str_replace(['\\r\\n', '\\n'], "\n", $texto));
    }
}
