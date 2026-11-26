<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Services\Simulacao\Data\ParametrosSimples;
use Illuminate\Foundation\Http\FormRequest;

class CompararRegimesRequest extends FormRequest
{
    use NormalizaEntradaFiscal;

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
            'atividade'            => ['required', 'string', 'size:2'],
            'aliquota_cbs_simples' => ['bail', 'required', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
            'aliquota_ibs_simples' => ['bail', 'required', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
            'compras'              => ['bail', 'nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'atividade'            => 'atividade do Simples',
            'aliquota_cbs_simples' => 'alíquota efetiva da CBS no Simples',
            'aliquota_ibs_simples' => 'alíquota efetiva do IBS no Simples',
            'compras'              => 'valor das compras',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'atividade.required'       => 'Selecione a atividade do Simples.',
            'required'                 => 'Informe a :attribute.',
            'numeric'                  => 'O campo :attribute deve ser um número.',
            'decimal'                  => 'O campo :attribute tem casas decimais demais.',
            'min'                      => 'O campo :attribute não pode ser negativo.',
            'aliquota_cbs_simples.max' => 'A alíquota deve ser no máximo 100%.',
            'aliquota_ibs_simples.max' => 'A alíquota deve ser no máximo 100%.',
        ];
    }

    public function parametros(): ParametrosSimples
    {
        return new ParametrosSimples(
            atividade: $this->string('atividade')->toString(),
            aliquotaCbs: $this->string('aliquota_cbs_simples')->toString(),
            aliquotaIbs: $this->string('aliquota_ibs_simples')->toString(),
            compras: $this->filled('compras') ? $this->string('compras')->toString() : null,
        );
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal([], ['aliquota_cbs_simples', 'aliquota_ibs_simples', 'compras']);
    }
}
