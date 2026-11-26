<?php

declare(strict_types=1);

namespace App\Importers\Data;

/**
 * Linha lida de um arquivo de catálogo, com as colunas já mapeadas para os nomes
 * internos (descricao, ncm, nbs, cst, c_class_trib, preco) e valores como texto.
 */
final readonly class LinhaPlanilha
{
    /**
     * @param  int  $linha  número da linha no arquivo (o cabeçalho é a linha 1)
     * @param  array<string, string|null>  $campos
     */
    public function __construct(
        public int $linha,
        public array $campos,
    ) {}
}
