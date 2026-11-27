<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogo;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Rules\Catalogo\RegrasImpostoSeletivo;
use App\Rules\Catalogo\RegrasProduto;
use App\Services\Simulacao\Data\DadosImpostoSeletivo;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Cadastro e edição de produto/serviço. A autorização fica na policy (controller).
 */
class ProdutoRequest extends FormRequest
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
        return RegrasProduto::regras();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return RegrasProduto::mensagens();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return RegrasImpostoSeletivo::atributos();
    }

    /**
     * @return array<string, array{description: string, example: mixed}>
     */
    public function bodyParameters(): array
    {
        return [
            'descricao'    => ['description' => 'Descrição do produto ou serviço.', 'example' => 'Mensalidade escolar'],
            'ncm'          => ['description' => 'NCM da mercadoria (8 dígitos). Informe NCM ou NBS.', 'example' => null],
            'nbs'          => ['description' => 'NBS do serviço (9 dígitos). Informe NCM ou NBS.', 'example' => '122011200'],
            'cst'          => ['description' => 'CST do IBS/CBS (3 dígitos).', 'example' => '200'],
            'c_class_trib' => ['description' => 'Código de classificação tributária (6 dígitos).', 'example' => '200028'],
            'preco'        => ['description' => 'Preço em reais, texto decimal com até 2 casas.', 'example' => '1200.00'],
            ...RegrasImpostoSeletivo::documentacao(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function dadosDoProduto(): array
    {
        return [
            'descricao'    => $this->string('descricao')->trim()->toString(),
            'ncm'          => $this->filled('ncm') ? $this->string('ncm')->toString() : null,
            'nbs'          => $this->filled('nbs') ? $this->string('nbs')->toString() : null,
            'cst'          => $this->string('cst')->toString(),
            'c_class_trib' => $this->string('c_class_trib')->toString(),
            'preco'        => $this->string('preco')->toString(),
            ...($this->filled('nbs') || $this->hasAny(DadosImpostoSeletivo::CAMPOS)
                ? RegrasImpostoSeletivo::paraGravar($this->filled('nbs') ? [] : $this->validated())
                : []),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal(['ncm', 'nbs', 'cst', 'c_class_trib'], ['preco']);
        $this->merge(array_intersect_key(RegrasImpostoSeletivo::normalizar($this->all()), $this->all()));
    }
}
