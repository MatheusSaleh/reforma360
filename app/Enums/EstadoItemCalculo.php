<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estado de cada item devolvido pela calculadora da Receita (campo "estadoItem").
 */
enum EstadoItemCalculo: string
{
    case Calculado = 'CALCULADO';
    case CalculoSimulado = 'CALCULO_SIMULADO';
    case InconsistenciaEntrada = 'INCONSISTENCIA_ENTRADA';
    case NaoImplementado = 'NAO_IMPLEMENTADO';
    case FalhaTecnica = 'FALHA_TECNICA';

    public function label(): string
    {
        return match ($this) {
            self::Calculado             => 'Calculado',
            self::CalculoSimulado       => 'Cálculo simulado',
            self::InconsistenciaEntrada => 'Inconsistência na entrada',
            self::NaoImplementado       => 'Não implementado',
            self::FalhaTecnica          => 'Falha técnica',
        };
    }

    public function foiCalculado(): bool
    {
        return $this === self::Calculado || $this === self::CalculoSimulado;
    }
}
