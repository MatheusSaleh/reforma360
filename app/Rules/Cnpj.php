<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * CNPJ numérico (14 dígitos) com dígitos verificadores válidos. Espera só dígitos.
 */
class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::valido($value)) {
            $fail('O CNPJ informado não é válido.');
        }
    }

    public static function valido(string $cnpj): bool
    {
        if (preg_match('/^\d{14}$/', $cnpj) !== 1 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        return self::digitoVerificador(mb_substr($cnpj, 0, 12)) === (int) $cnpj[12]
            && self::digitoVerificador(mb_substr($cnpj, 0, 13)) === (int) $cnpj[13];
    }

    /**
     * Gera um CNPJ fictício válido (para factories e seeders).
     */
    public static function gerar(): string
    {
        $base = '';

        for ($i = 0; $i < 8; $i++) {
            $base .= (string) random_int(0, 9);
        }

        $cnpj = $base.'0001';
        $cnpj .= self::digitoVerificador($cnpj);

        return $cnpj.self::digitoVerificador($cnpj);
    }

    private static function digitoVerificador(string $parcial): int
    {
        $soma = 0;
        $peso = mb_strlen($parcial) - 7;

        foreach (mb_str_split($parcial) as $digito) {
            $soma += (int) $digito * $peso;
            $peso = $peso === 2 ? 9 : $peso - 1;
        }

        return $soma % 11 < 2 ? 0 : 11 - $soma % 11;

    }
}
