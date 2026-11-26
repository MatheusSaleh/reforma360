<?php

declare(strict_types=1);

namespace App\Http\Requests\Importacao;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportarCatalogoRequest extends FormRequest
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
            'arquivo' => [
                'required',
                'file',
                'extensions:csv,txt,xlsx',
                'mimes:csv,txt,xlsx',
                'max:'.config()->integer('importacao.tamanho_maximo_kb'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'arquivo.required'   => 'Selecione a planilha.',
            'arquivo.extensions' => 'Envie um arquivo .csv ou .xlsx.',
            'arquivo.mimes'      => 'Envie um arquivo .csv ou .xlsx.',
            'arquivo.max'        => 'O arquivo deve ter no máximo :max KB.',
        ];
    }

    public function arquivo(): UploadedFile
    {
        $arquivo = $this->file('arquivo');
        abort_unless($arquivo instanceof UploadedFile, 422);

        return $arquivo;
    }
}
