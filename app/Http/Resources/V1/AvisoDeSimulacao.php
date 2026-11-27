<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

/**
 * Texto de aviso incluído em toda resposta da API que traz cálculo.
 */
final class AvisoDeSimulacao
{
    public const string TEXTO = 'Simulação feita com a calculadora oficial da Receita Federal. Não substitui a orientação de um contador.';
}
