<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use InvalidArgumentException;

/**
 * Validação de valores decimais enviados à calculadora (sempre string, ponto como separador).
 */
final class Decimal
{
    public static function validar(?string $valor, string $campo): ?string
    {
        if ($valor !== null && preg_match('/^\d+(\.\d+)?$/', $valor) !== 1) {
            throw new InvalidArgumentException("O campo {$campo} deve ser um decimal não negativo com ponto (ex.: \"100.00\"), recebido: \"{$valor}\".");
        }

        return $valor;
    }
}
