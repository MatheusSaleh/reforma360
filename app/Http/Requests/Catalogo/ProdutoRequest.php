<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogo;

use App\Http\Requests\Concerns\NormalizaEntradaFiscal;
use App\Rules\Catalogo\RegrasProduto;
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
