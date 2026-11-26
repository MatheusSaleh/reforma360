<?php

declare(strict_types=1);

namespace App\Importers;

use App\Enums\FormatoImportacao;
use App\Importers\Data\LinhaPlanilha;
use App\Importers\Exceptions\ArquivoInvalidoException;
use Closure;

/**
 * Estratégia de leitura de um formato de arquivo de catálogo.
 *
 * O importador só lê e normaliza; validação e persistência ficam no ImportarCatalogoService.
 */
interface CatalogImporter
{
    public function suporta(FormatoImportacao $formato): bool;

    /**
     * Lê o arquivo (no disco "local") em lotes, sem carregá-lo inteiro em memória.
     *
     * @param  Closure(list<LinhaPlanilha>): void  $aoLerLote
     *
     * @throws ArquivoInvalidoException
     */
    public function ler(string $caminho, Closure $aoLerLote): void;
}
