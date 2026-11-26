<?php

declare(strict_types=1);

namespace App\Importers\Exceptions;

use RuntimeException;

/**
 * O arquivo inteiro não pode ser importado (cabeçalho inválido, vazio, grande demais).
 * A mensagem é mostrada ao usuário.
 */
final class ArquivoInvalidoException extends RuntimeException
{
    /**
     * @param  list<string>  $faltando
     */
    public static function colunasFaltando(array $faltando): self
    {
        return new self('Cabeçalho inválido: faltam as colunas '.implode(', ', $faltando).'. Use a planilha modelo.');
    }

    public static function semLinhas(): self
    {
        return new self('A planilha não tem linhas de produtos.');
    }

    public static function linhasDemais(int $maximo): self
    {
        return new self("A planilha passa do limite de {$maximo} linhas. Divida o arquivo e importe em partes.");
    }
}
