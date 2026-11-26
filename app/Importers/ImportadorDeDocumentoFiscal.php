<?php

declare(strict_types=1);

namespace App\Importers;

use App\Importers\Data\DocumentoFiscal;
use App\Importers\Exceptions\ArquivoInvalidoException;

/**
 * Importador de nota fiscal: além das linhas, informa os dados da operação.
 */
interface ImportadorDeDocumentoFiscal extends CatalogImporter
{
    /**
     * @throws ArquivoInvalidoException
     */
    public function documento(string $caminho): DocumentoFiscal;
}
