<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogo;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
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
        return [
            'descricao'    => ['required', 'string', 'max:255'],
            'ncm'          => ['nullable', 'required_without:nbs', 'prohibits:nbs', 'digits:8'],
            'nbs'          => ['nullable', 'required_without:ncm', 'digits:9'],
            'cst'          => ['required', 'digits:3'],
            'c_class_trib' => ['required', 'digits:6'],
            'preco'        => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ncm.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'nbs.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'ncm.prohibits'        => 'Informe apenas o NCM ou apenas o NBS.',
            'preco.decimal'        => 'O preço deve ter no máximo 2 casas decimais.',
            'preco.gt'             => 'O preço deve ser maior que zero.',
        ];
    }

    /**
     * @return array{descricao: string, ncm: string|null, nbs: string|null, cst: string, c_class_trib: string, preco: string}
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarEntradaFiscal(['ncm', 'nbs', 'cst', 'c_class_trib'], ['preco']);
    }
}
