<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Formatação pt-BR de decimais em string, sem passar por float.
 */
final class Formatador
{
    public static function moeda(string $valor): string
    {
        [$inteiro, $fracao] = self::partes($valor);

        return 'R$ '.self::milhares($inteiro).','.mb_str_pad(mb_substr($fracao, 0, 2), 2, '0');
    }

    public static function percentual(string $valor): string
    {
        [$inteiro, $fracao] = self::partes($valor);

        return self::milhares($inteiro).($fracao === '' ? '' : ','.$fracao).'%';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function partes(string $valor): array
    {
        $partes = explode('.', $valor, 2);

        return [mb_ltrim($partes[0], '0') ?: '0', $partes[1] ?? ''];
    }

    private static function milhares(string $inteiro): string
    {
        return (string) preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $inteiro);
    }
}
