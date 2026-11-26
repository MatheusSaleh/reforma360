<?php

declare(strict_types=1);

namespace App\Services\Simulacao\Data;

use App\Enums\TipoDocumentoFiscal;

/**
 * XML do grupo IBS/CBS de uma simulação, já validado contra o XSD oficial.
 */
final readonly class XmlDoGrupo
{
    public function __construct(
        public string $conteudo,
        public TipoDocumentoFiscal $tipo,
        public int $quantidadeItens,
        public string $nomeArquivo,
    ) {}
}
