<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoDocumentoFiscal: string
{
    case Nfe = 'nfe';
    case Nfce = 'nfce';
    case Nfse = 'nfse';

    public function label(): string
    {
        return match ($this) {
            self::Nfe  => 'NF-e',
            self::Nfce => 'NFC-e',
            self::Nfse => 'NFS-e',
        };
    }

    /**
     * A calculadora gera o XML do grupo IBS/CBS só para NF-e e NFC-e (não para NFS-e).
     */
    public function geraXmlDoGrupo(): bool
    {
        return $this !== self::Nfse;
    }
}
