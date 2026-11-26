<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Http\Requests\Concerns\ValidaParametrosOperacao;
use Illuminate\Foundation\Http\FormRequest;

class IniciarSimulacaoRequest extends FormRequest
{
    use NormalizaEntradaFiscal;
    use ValidaParametrosOperacao;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->regrasDosParametros();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->atributosDosParametros();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mensagensDosParametros();
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal(
            camposSoDigitos: ['municipio'],
            camposDecimais: ['aliquota_cbs', 'aliquota_ibs_estadual', 'aliquota_ibs_municipal'],
        );
    }
}
