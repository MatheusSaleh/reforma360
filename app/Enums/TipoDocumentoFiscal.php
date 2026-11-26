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
}
