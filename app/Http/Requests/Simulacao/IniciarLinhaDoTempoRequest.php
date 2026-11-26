<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use Illuminate\Foundation\Http\FormRequest;

class IniciarLinhaDoTempoRequest extends FormRequest
{
    use NormalizaEntradaFiscal;

    private const array CAMPOS = ['aliquota_plena_cbs', 'aliquota_plena_ibs_estadual', 'aliquota_plena_ibs_municipal'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_fill_keys(self::CAMPOS, ['bail', 'required', 'numeric', 'decimal:0,4', 'min:0', 'max:100']);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'aliquota_plena_cbs'           => 'alíquota plena da CBS',
            'aliquota_plena_ibs_estadual'  => 'alíquota plena do IBS estadual',
            'aliquota_plena_ibs_municipal' => 'alíquota plena do IBS municipal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Informe a :attribute.',
            'numeric'  => 'A :attribute deve ser um número.',
            'decimal'  => 'A :attribute deve ter no máximo 4 casas decimais.',
            'min'      => 'A :attribute não pode ser negativa.',
            'max'      => 'A :attribute deve ser no máximo 100%.',
        ];
    }

    public function aliquotasPlenas(): AliquotasNominais
    {
        return new AliquotasNominais(
            cbs: $this->string('aliquota_plena_cbs')->toString(),
            ibsEstadual: $this->string('aliquota_plena_ibs_estadual')->toString(),
            ibsMunicipal: $this->string('aliquota_plena_ibs_municipal')->toString(),
        );
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal([], self::CAMPOS);
    }
}
