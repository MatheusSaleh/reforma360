<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoItem: string
{
    case Mercadoria = 'mercadoria';
    case Servico = 'servico';

    public function label(): string
    {
        return match ($this) {
            self::Mercadoria => 'Mercadoria',
            self::Servico    => 'Serviço',
        };
    }
}
