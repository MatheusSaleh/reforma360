<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Hierarquia de um NCM/NBS (capítulo › posição › subposição › item › subitem).
 */
final readonly class DetalheNomenclatura
{
    use LeCamposResposta;

    private const array NIVEIS = ['capitulo', 'posicao', 'subposicao', 'item', 'subitem'];

    /**
     * @param  list<string>  $niveis
     */
    public function __construct(
        public string $codigo,
        public array $niveis,
        public bool $tributadoPeloImpostoSeletivo,
    ) {}

    /**
     * @param  array<array-key, mixed>  $dados
     */
    public static function fromArray(string $codigo, array $dados): self
    {
        $niveis = [];

        foreach (self::NIVEIS as $nivel) {
            $texto = self::textoOpcional($dados, $nivel);

            if ($texto !== null && mb_trim($texto) !== '') {
                $niveis[] = mb_rtrim(mb_trim($texto), '.');
            }
        }

        return new self(
            codigo: $codigo,
            niveis: $niveis,
            tributadoPeloImpostoSeletivo: ($dados['tributadoPeloImpostoSeletivo'] ?? false) === true,
        );
    }
}
