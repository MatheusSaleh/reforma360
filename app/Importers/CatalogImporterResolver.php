<?php

declare(strict_types=1);

namespace App\Importers;

use App\Enums\FormatoImportacao;
use Illuminate\Container\Attributes\Tag;
use InvalidArgumentException;

/**
 * Escolhe a estratégia de leitura pelo formato. Novo formato = nova classe
 * marcada com a tag "catalog-importers" no AppServiceProvider.
 */
final readonly class CatalogImporterResolver
{
    /**
     * @param  iterable<CatalogImporter>  $importadores
     */
    public function __construct(
        #[Tag('catalog-importers')]
        private iterable $importadores,
    ) {}

    public function para(FormatoImportacao $formato): CatalogImporter
    {
        foreach ($this->importadores as $importador) {
            if ($importador->suporta($formato)) {
                return $importador;
            }
        }

        throw new InvalidArgumentException("Nenhum importador para o formato {$formato->value}.");
    }
}
