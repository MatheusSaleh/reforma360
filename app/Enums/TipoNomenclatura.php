<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Nomenclatura fiscal do item: NCM (mercadorias, 8 dígitos) ou NBS (serviços, 9 dígitos).
 */
enum TipoNomenclatura: string
{
    case Ncm = 'ncm';
    case Nbs = 'nbs';

    public function digitos(): int
    {
        return match ($this) {
            self::Ncm => 8,
            self::Nbs => 9,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Ncm => 'NCM',
            self::Nbs => 'NBS',
        };
    }
}
