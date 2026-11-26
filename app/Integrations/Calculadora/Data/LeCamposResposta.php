<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;

/**
 * Leitura tipada dos campos da resposta da calculadora.
 *
 * Valores monetários e alíquotas chegam como string decimal ("42.24") e continuam string:
 * nunca passam por float.
 */
trait LeCamposResposta
{
    /**
     * @param  array<array-key, mixed>  $dados
     */
    private static function texto(array $dados, string $campo): string
    {
        return self::textoOpcional($dados, $campo)
            ?? throw RespostaInesperadaException::campoAusente($campo);
    }

    /**
     * @param  array<array-key, mixed>  $dados
     */
    private static function textoOpcional(array $dados, string $campo): ?string
    {
        $valor = $dados[$campo] ?? null;

        return match (true) {
            $valor === null   => null,
            is_string($valor) => $valor,
            is_int($valor)    => (string) $valor,
            default           => throw RespostaInesperadaException::campoInvalido($campo),
        };
    }

    /**
     * @param  array<array-key, mixed>  $dados
     */
    private static function inteiro(array $dados, string $campo): int
    {
        $valor = $dados[$campo] ?? null;

        if (! is_int($valor)) {
            throw RespostaInesperadaException::campoInvalido($campo);
        }

        return $valor;
    }

    /**
     * @param  array<array-key, mixed>  $dados
     * @return array<array-key, mixed>|null
     */
    private static function grupoOpcional(array $dados, string $campo): ?array
    {
        $valor = $dados[$campo] ?? null;

        if ($valor !== null && ! is_array($valor)) {
            throw RespostaInesperadaException::campoInvalido($campo);
        }

        return $valor;
    }

    /**
     * @param  array<array-key, mixed>  $dados
     * @return array<array-key, mixed>
     */
    private static function grupo(array $dados, string $campo): array
    {
        return self::grupoOpcional($dados, $campo)
            ?? throw RespostaInesperadaException::campoAusente($campo);
    }

    /**
     * @param  array<array-key, mixed>  $dados
     * @return list<array<array-key, mixed>>
     */
    private static function listaDeGrupos(array $dados, string $campo): array
    {
        $lista = [];

        foreach (self::grupoOpcional($dados, $campo) ?? [] as $item) {
            if (! is_array($item)) {
                throw RespostaInesperadaException::campoInvalido($campo);
            }

            $lista[] = $item;
        }

        return $lista;
    }
}
