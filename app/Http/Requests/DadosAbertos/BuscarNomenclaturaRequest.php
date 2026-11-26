<?php

declare(strict_types=1);

namespace App\Http\Requests\DadosAbertos;

use Illuminate\Foundation\Http\FormRequest;

class BuscarNomenclaturaRequest extends FormRequest
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
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['q' => 'termo de busca'];
    }
}
