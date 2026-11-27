<?php

declare(strict_types=1);

namespace App\Http\Requests\Simulacao;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Http\Requests\Concerns\ValidaParametrosOperacao;
use App\Rules\Catalogo\RegrasImpostoSeletivo;
use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use App\Services\Simulacao\Data\DadosSimulacaoItem;
use Illuminate\Foundation\Http\FormRequest;

class SimularItemRequest extends FormRequest
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
            'ncm'          => ['nullable', 'required_without:nbs', 'prohibits:nbs', 'digits:8'],
            'nbs'          => ['nullable', 'required_without:ncm', 'digits:9'],
            'cst'          => ['required', 'digits:3'],
            'c_class_trib' => ['required', 'digits:6'],
            'valor'        => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            ...$this->regrasDosParametros(),
            ...RegrasImpostoSeletivo::regras(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ncm'          => 'NCM',
            'nbs'          => 'NBS',
            'cst'          => 'CST',
            'c_class_trib' => 'cClassTrib',
            'valor'        => 'valor',
            ...$this->atributosDosParametros(),
            ...RegrasImpostoSeletivo::atributos(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->mensagensDosParametros(),
            ...RegrasImpostoSeletivo::mensagens(),
            'ncm.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'nbs.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'ncm.prohibits'        => 'Informe apenas o NCM ou apenas o NBS.',
            'valor.decimal'        => 'O valor deve ter no máximo 2 casas decimais.',
            'valor.gt'             => 'O valor deve ser maior que zero.',
            'valor.max'            => 'O valor é maior que o permitido.',
        ];
    }

    /**
     * @return array<string, array{description: string, example: mixed}>
     */
    public function bodyParameters(): array
    {
        return [
            'ncm'          => ['description' => 'NCM da mercadoria (8 dígitos). Informe NCM ou NBS.', 'example' => null],
            'nbs'          => ['description' => 'NBS do serviço (9 dígitos). Informe NCM ou NBS.', 'example' => '122011200'],
            'cst'          => ['description' => 'CST do IBS/CBS (3 dígitos).', 'example' => '200'],
            'c_class_trib' => ['description' => 'Código de classificação tributária (6 dígitos).', 'example' => '200028'],
            'valor'        => ['description' => 'Valor da operação em reais, texto decimal com até 2 casas.', 'example' => '1200.00'],
            ...$this->documentacaoDosParametros(),
            ...array_replace(RegrasImpostoSeletivo::documentacao(), [
                'is_quantidade' => ['description' => 'Quantidade total do item na unidade do IS.', 'example' => null],
            ]),
        ];
    }

    public function dados(): DadosSimulacaoItem
    {
        $parametros = $this->parametros();

        return new DadosSimulacaoItem(
            cst: $this->string('cst')->toString(),
            cClassTrib: $this->string('c_class_trib')->toString(),
            valor: $this->string('valor')->toString(),
            uf: $parametros->uf,
            municipio: $parametros->municipio,
            data: $parametros->data,
            ncm: $this->filled('ncm') ? $this->string('ncm')->toString() : null,
            nbs: $this->filled('nbs') ? $this->string('nbs')->toString() : null,
            aliquotasNominais: $parametros->aliquotasNominais,
            impostoSeletivo: DadosImpostoSeletivo::deCampos($this->validated()),
        );
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal(
            camposSoDigitos: ['ncm', 'nbs', 'cst', 'c_class_trib', 'municipio'],
            camposDecimais: ['valor', 'aliquota_cbs', 'aliquota_ibs_estadual', 'aliquota_ibs_municipal'],
        );
        $this->merge(RegrasImpostoSeletivo::normalizar($this->all()));
    }
}
