<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusSimulacao: string
{
    case Pendente = 'pendente';
    case Processando = 'processando';
    case Concluida = 'concluida';
    case ConcluidaComErros = 'concluida_com_erros';
    case Falhou = 'falhou';

    public function label(): string
    {
        return match ($this) {
            self::Pendente          => 'Na fila',
            self::Processando       => 'Simulando',
            self::Concluida         => 'Concluída',
            self::ConcluidaComErros => 'Concluída com erros',
            self::Falhou            => 'Falhou',
        };
    }

    public function emAndamento(): bool
    {
        return $this === self::Pendente || $this === self::Processando;
    }
}
