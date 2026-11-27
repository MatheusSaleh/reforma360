<?php

declare(strict_types=1);

namespace App\Http\Requests\DadosAbertos;

use Illuminate\Foundation\Http\FormRequest;

class DetalheNomenclaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'data' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
