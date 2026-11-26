<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Exceptions;

use RuntimeException;

/**
 * A resposta da calculadora não tem o formato esperado (ex.: contrato do upstream mudou).
 */
final class RespostaInesperadaException extends RuntimeException
{
    public static function campoAusente(string $campo): self
    {
        return new self("Campo obrigatório ausente na resposta da calculadora: {$campo}.");
    }

    public static function campoInvalido(string $campo): self
    {
        return new self("Campo com tipo inesperado na resposta da calculadora: {$campo}.");
    }

    public static function corpoInvalido(): self
    {
        return new self('A calculadora devolveu um corpo que não é JSON válido.');
    }
}
