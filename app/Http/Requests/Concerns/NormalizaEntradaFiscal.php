<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Support\NormalizadorFiscal;

/**
 * Normalização da entrada antes da validação: códigos fiscais só com dígitos
 * e decimais pt-BR ("1.234,56") convertidos para ponto ("1234.56").
 *
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait NormalizaEntradaFiscal
{
    /**
     * @param  list<string>  $camposSoDigitos
     * @param  list<string>  $camposDecimais
     */
    protected function normalizarEntradaFiscal(array $camposSoDigitos, array $camposDecimais = []): void
    {
        $normalizados = [];

        foreach ($camposSoDigitos as $campo) {
            if (is_string($this->input($campo))) {
                $normalizados[$campo] = NormalizadorFiscal::digitos($this->input($campo));
            }
        }

        foreach ($camposDecimais as $campo) {
            if (is_string($this->input($campo))) {
                $normalizados[$campo] = NormalizadorFiscal::decimal($this->input($campo));
            }
        }

        $this->merge($normalizados);
    }
}
