<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Enums\Uf;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Services\Simulacao\Data\ParametrosSimulacao;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Parâmetros da operação (data, UF, município e alíquotas nominais), comuns ao
 * simulador de um item e à simulação em lote.
 *
 * @mixin \Illuminate\Foundation\Http\FormRequest
 */
trait ValidaParametrosOperacao
{
    /**
     * @return array<string, mixed>
     */
    protected function regrasDosParametros(): array
    {
        $exigeAliquotas = Rule::requiredIf(fn (): bool => $this->exigeAliquotasNominais());

        return [
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
    protected function atributosDosParametros(): array
    {
        return [
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
    protected function mensagensDosParametros(): array
    {
        return [
            'required'             => 'Informe :attribute.',
            'integer'              => ':Attribute deve ser um código numérico.',
            'digits'               => ':Attribute deve ter :digits dígitos.',
            'numeric'              => ':Attribute deve ser um número.',
            'decimal'              => ':Attribute deve ter no máximo 4 casas decimais.',
            'min'                  => ':Attribute não pode ser negativa.',
            'max'                  => ':Attribute deve ser no máximo :max%.',
            'uf.enum'              => 'Selecione uma UF válida.',
            'date_format'          => 'Informe uma data válida.',
            'data.after_or_equal'  => 'A data deve estar entre 2026 e 2033 (período de transição).',
            'data.before_or_equal' => 'A data deve estar entre 2026 e 2033 (período de transição).',
        ];
    }

    public function parametros(): ParametrosSimulacao
    {
        return new ParametrosSimulacao(
            data: CarbonImmutable::parse($this->string('data')->toString(), 'America/Sao_Paulo')->startOfDay(),
            uf: Uf::from($this->string('uf')->toString()),
            municipio: $this->integer('municipio'),
            aliquotasNominais: $this->filled('aliquota_cbs')
                ? new AliquotasNominais(
                    cbs: $this->string('aliquota_cbs')->toString(),
                    ibsEstadual: $this->string('aliquota_ibs_estadual')->toString(),
                    ibsMunicipal: $this->string('aliquota_ibs_municipal')->toString(),
                )
                : null,
        );
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
