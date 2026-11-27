<?php

declare(strict_types=1);

namespace App\Http\Requests\Conta;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarTokenDeApiRequest extends FormRequest
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
        return [
            'nome'         => ['required', 'string', 'max:100'],
            'permissoes'   => ['required', 'array', 'min:1'],
            'permissoes.*' => ['string', 'distinct', Rule::in(array_keys($this->permissoesDisponiveis()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required'       => 'Dê um nome ao token, para lembrar onde ele é usado.',
            'nome.max'            => 'O nome pode ter no máximo :max caracteres.',
            'permissoes.required' => 'Escolha ao menos uma permissão.',
            'permissoes.min'      => 'Escolha ao menos uma permissão.',
            'permissoes.*.in'     => 'Permissão inválida.',
        ];
    }

    public function nome(): string
    {
        return $this->string('nome')->trim()->toString();
    }

    /**
     * @return list<string>
     */
    public function permissoes(): array
    {
        /** @var list<string> $permissoes */
        $permissoes = array_values($this->validated('permissoes'));

        return $permissoes;
    }

    /**
     * @return array<string, string>
     */
    private function permissoesDisponiveis(): array
    {
        /** @var array<string, string> $permissoes */
        $permissoes = config('api.permissoes');

        return $permissoes;
    }
}
