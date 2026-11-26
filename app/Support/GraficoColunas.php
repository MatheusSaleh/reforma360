<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Geometria de um gráfico de colunas empilhadas (2 séries) calculada sem float:
 * escala com teto "redondo" (1, 2 ou 5 × 10^n) e alturas em pixels inteiros.
 */
final class GraficoColunas
{
    /**
     * @param  list<array{rotulo: string, valores: list<numeric-string|null>}>  $colunas
     * @return array{teto: numeric-string, ticks: list<numeric-string>, colunas: list<array{rotulo: string, valores: list<numeric-string|null>, alturas: list<int>, total: numeric-string|null}>}
     */
    public static function montar(array $colunas, int $alturaPx): array
    {
        $maximo = '0';

        foreach ($colunas as $coluna) {
            $soma = self::soma($coluna['valores']);

            if ($soma !== null && bccomp($soma, $maximo, 2) > 0) {
                $maximo = $soma;
            }
        }

        [$teto, $divisoes] = self::tetoRedondo($maximo);
        $ticks = [];

        for ($i = 0; $i <= $divisoes; $i++) {
            $ticks[] = bcdiv(bcmul($teto, (string) $i, 2), (string) $divisoes, 2);
        }

        return [
            'teto'    => $teto,
            'ticks'   => $ticks,
            'colunas' => array_map(fn (array $coluna): array => [
                ...$coluna,
                'alturas' => array_map(
                    fn (?string $valor): int => $valor === null ? 0 : (int) bcdiv(bcmul($valor, (string) $alturaPx, 2), $teto, 0),
                    $coluna['valores'],
                ),
                'total' => self::soma($coluna['valores']),
            ], $colunas),
        ];
    }

    /**
     * @param  list<numeric-string|null>  $valores
     * @return numeric-string|null
     */
    private static function soma(array $valores): ?string
    {
        if (in_array(null, $valores, true)) {
            return null;
        }

        return array_reduce($valores, fn (string $soma, string $valor): string => bcadd($soma, $valor, 2), '0');
    }

    /**
     * Menor valor 1, 2 ou 5 × 10^n que seja ≥ o máximo (mínimo 1), com o número de
     * divisões que gera ticks redondos (1 e 5 → 5 divisões; 2 → 4).
     *
     * @param  numeric-string  $maximo
     * @return array{0: numeric-string, 1: int}
     */
    private static function tetoRedondo(string $maximo): array
    {
        if (bccomp($maximo, '1', 2) <= 0) {
            return ['1', 5];
        }

        $potencia = '1';

        while (true) {
            foreach (['1', '2', '5'] as $fator) {
                $candidato = bcmul($fator, $potencia, 2);

                if (bccomp($candidato, $maximo, 2) >= 0) {
                    return [bcadd($candidato, '0', 0), $fator === '2' ? 4 : 5];
                }
            }

            $potencia = bcmul($potencia, '10', 0);
        }
    }
}
