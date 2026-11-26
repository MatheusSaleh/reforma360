<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * CST de CBS/IBS com as classificações tributárias (cClassTrib) vinculadas.
 */
final readonly class SituacaoTributaria
{
    use LeCamposResposta;

    /**
     * @param  list<array{codigo: string, descricao: string}>  $classificacoes
     */
    public function __construct(
        public string $codigo,
        public string $descricao,
        public array $classificacoes,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(array $dados): self
    {
        return new self(
            codigo: self::texto($dados, 'codigo'),
            descricao: self::texto($dados, 'descricao'),
            classificacoes: array_map(
                fn (array $classificacao): array => [
                    'codigo'    => self::texto($classificacao, 'codigo'),
                    'descricao' => self::texto($classificacao, 'descricao'),
                ],
                self::listaDeGrupos($dados, 'classificacoesTributarias'),
            ),
        );
    }
}
