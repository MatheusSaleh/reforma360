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

    public static function xmlInseguro(): self
    {
        return new self('O XML contém declarações (DOCTYPE/ENTITY) que não existem em notas fiscais e foi recusado por segurança.');
    }

    public static function xmlMalformado(string $detalhe): self
    {
        return new self('O arquivo não é um XML válido: '.$detalhe);
    }

    public static function naoEhNotaFiscal(): self
    {
        return new self('O XML não é uma NF-e, NFC-e ou NFS-e reconhecida.');
    }

    public static function maisDeUmaNota(int $quantidade): self
    {
        return new self("O arquivo tem {$quantidade} notas. Envie uma nota por arquivo.");
    }

    public static function campoObrigatorioDaNota(string $campo): self
    {
        return new self("A nota não tem o campo obrigatório {$campo}.");
    }
}
