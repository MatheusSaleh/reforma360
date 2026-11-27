<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Http\Requests\Concerns\ValidaParametrosOperacao;
use App\Models\Importacao;
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
        return [
            ...$this->regrasDosParametros(),
            'importacao_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, array{description: string, example: mixed}>
     */
    public function bodyParameters(): array
    {
        return [
            ...$this->documentacaoDosParametros(),
            'importacao_id' => ['description' => 'Simula só os itens desta nota importada. Sem ele, simula o catálogo inteiro.', 'example' => null],
        ];
    }

    /**
     * Nota importada a simular (só os itens dela). A autorização fica no controller.
     */
    public function importacao(): ?Importacao
    {
        return $this->filled('importacao_id') ? Importacao::query()->findOrFail($this->integer('importacao_id')) : null;
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
