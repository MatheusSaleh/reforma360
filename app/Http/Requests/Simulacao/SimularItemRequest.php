<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Enums\Uf;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Services\Simulacao\Data\DadosSimulacaoItem;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimularItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $exigeAliquotas = Rule::requiredIf(fn (): bool => $this->exigeAliquotasNominais());

        return [
            'ncm'                    => ['nullable', 'required_without:nbs', 'prohibits:nbs', 'digits:8'],
            'nbs'                    => ['nullable', 'required_without:ncm', 'digits:9'],
            'cst'                    => ['required', 'digits:3'],
            'c_class_trib'           => ['required', 'digits:6'],
            'valor'                  => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'uf'                     => ['required', Rule::enum(Uf::class)],
            'municipio'              => ['required', 'integer', 'digits:7', $this->municipioPertenceAUf(...)],
            'data'                   => ['required', 'date_format:Y-m-d', 'after_or_equal:'.config('simulacao.data_minima'), 'before_or_equal:'.config('simulacao.data_maxima')],
            'aliquota_cbs'           => [$exigeAliquotas, 'nullable', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
            'aliquota_ibs_estadual'  => [$exigeAliquotas, 'nullable', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
            'aliquota_ibs_municipal' => [$exigeAliquotas, 'nullable', 'numeric', 'decimal:0,4', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ncm'                    => 'NCM',
            'nbs'                    => 'NBS',
            'cst'                    => 'CST',
            'c_class_trib'           => 'cClassTrib',
            'valor'                  => 'valor',
            'uf'                     => 'UF',
            'municipio'              => 'município',
            'data'                   => 'data do fato gerador',
            'aliquota_cbs'           => 'alíquota da CBS',
            'aliquota_ibs_estadual'  => 'alíquota do IBS estadual',
            'aliquota_ibs_municipal' => 'alíquota do IBS municipal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required'             => 'Informe :attribute.',
            'ncm.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'nbs.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'ncm.prohibits'        => 'Informe apenas o NCM ou apenas o NBS.',
            'digits'               => ':Attribute deve ter :digits dígitos.',
            'numeric'              => ':Attribute deve ser um número.',
            'integer'              => ':Attribute deve ser um código numérico.',
            'valor.decimal'        => 'O valor deve ter no máximo 2 casas decimais.',
            'decimal'              => ':Attribute deve ter no máximo 4 casas decimais.',
            'valor.gt'             => 'O valor deve ser maior que zero.',
            'valor.max'            => 'O valor é maior que o permitido.',
            'min'                  => ':Attribute não pode ser negativa.',
            'max'                  => ':Attribute deve ser no máximo :max%.',
            'uf.enum'              => 'Selecione uma UF válida.',
            'date_format'          => 'Informe uma data válida.',
            'data.after_or_equal'  => 'A data deve estar entre 2026 e 2033 (período de transição).',
            'data.before_or_equal' => 'A data deve estar entre 2026 e 2033 (período de transição).',
        ];
    }

    public function dados(): DadosSimulacaoItem
    {
        $aliquotas = $this->filled('aliquota_cbs')
            ? new AliquotasNominais(
                cbs: $this->string('aliquota_cbs')->toString(),
                ibsEstadual: $this->string('aliquota_ibs_estadual')->toString(),
                ibsMunicipal: $this->string('aliquota_ibs_municipal')->toString(),
            )
            : null;

        return new DadosSimulacaoItem(
            cst: $this->string('cst')->toString(),
            cClassTrib: $this->string('c_class_trib')->toString(),
            valor: $this->string('valor')->toString(),
            uf: Uf::from($this->string('uf')->toString()),
            municipio: $this->integer('municipio'),
            data: CarbonImmutable::parse($this->string('data')->toString(), 'America/Sao_Paulo')->startOfDay(),
            ncm: $this->filled('ncm') ? $this->string('ncm')->toString() : null,
            nbs: $this->filled('nbs') ? $this->string('nbs')->toString() : null,
            aliquotasNominais: $aliquotas,
        );
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['ncm', 'nbs', 'cst', 'c_class_trib', 'municipio'] as $campo) {
            if (is_string($this->input($campo))) {
                $normalizados[$campo] = preg_replace('/\D/', '', $this->input($campo));
            }
        }

        foreach (['valor', 'aliquota_cbs', 'aliquota_ibs_estadual', 'aliquota_ibs_municipal'] as $campo) {
            if (is_string($this->input($campo))) {
                $normalizados[$campo] = self::decimalComPonto($this->input($campo));
            }
        }

        $this->merge($normalizados);
    }

    /**
     * Aceita "1.234,56" (pt-BR) ou "1234.56".
     */
    private static function decimalComPonto(string $valor): string
    {
        $valor = mb_trim(str_replace(['R$', '%', ' '], '', $valor));

        return str_contains($valor, ',') ? str_replace(['.', ','], ['', '.'], $valor) : $valor;
    }

    private function exigeAliquotasNominais(): bool
    {
        $data = $this->input('data');

        if (! is_string($data) || preg_match('/^(\d{4})-\d{2}-\d{2}$/', $data, $partes) !== 1) {
            return false;
        }

        return (int) $partes[1] >= config()->integer('simulacao.ano_inicio_aliquotas_nominais');
    }

    private function municipioPertenceAUf(string $atributo, mixed $valor, Closure $falhar): void
    {
        $uf = Uf::tryFrom($this->string('uf')->toString());

        if ($uf !== null && is_numeric($valor) && ! $uf->contemMunicipio((int) $valor)) {
            $falhar('O município não pertence à UF selecionada.');
        }
    }
}
