<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalização de entrada fiscal: códigos só com dígitos e decimais pt-BR com ponto.
 */
final class NormalizadorFiscal
{
    public static function digitos(string $valor): string
    {
        return (string) preg_replace('/\D/', '', $valor);
    }

    /**
     * Aceita "1.234,56" (pt-BR), "1234.56" e "R$ 1.234,56".
     */
    public static function decimal(string $valor): string
    {
        $valor = mb_trim(str_replace(['R$', '%', ' ', "\u{00A0}"], '', $valor));

        return str_contains($valor, ',') ? str_replace(['.', ','], ['', '.'], $valor) : $valor;
    }

    /**
     * Código numérico com zeros à esquerda restaurados (planilhas costumam perdê-los:
     * NCM 01012100 vira 1012100). Só completa quando o valor tem apenas dígitos.
     */
    public static function codigo(string $valor, int $tamanho): string
    {
        $digitos = self::digitos($valor);

        if ($digitos === '' || mb_strlen($digitos) >= $tamanho) {
            return $digitos;
        }

        return mb_str_pad($digitos, $tamanho, '0', STR_PAD_LEFT);
    }
}
