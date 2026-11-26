<?php

declare(strict_types=1);

namespace App\Importers\Data;

use App\Enums\TipoDocumentoFiscal;
use App\Enums\Uf;
use Carbon\CarbonImmutable;

/**
 * Dados da operação lidos de uma nota fiscal (usados para simular a nota).
 */
final readonly class DocumentoFiscal
{
    public function __construct(
        public TipoDocumentoFiscal $tipo,
        public string $numero,
        public CarbonImmutable $data,
        public ?Uf $uf,
        public ?int $municipio,
    ) {}
}
