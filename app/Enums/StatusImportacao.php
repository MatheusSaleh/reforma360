<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusImportacao: string
{
    case Pendente = 'pendente';
    case Processando = 'processando';
    case Concluida = 'concluida';
    case Falhou = 'falhou';

    public function label(): string
    {
        return match ($this) {
            self::Pendente    => 'Na fila',
            self::Processando => 'Processando',
            self::Concluida   => 'Concluída',
            self::Falhou      => 'Falhou',
        };
    }

    public function emAndamento(): bool
    {
        return $this === self::Pendente || $this === self::Processando;
    }
}
