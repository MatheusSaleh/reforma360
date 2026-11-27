<?php

declare(strict_types=1);

namespace App\Rules\Catalogo;

/**
 * Regras de validação de produto/serviço, usadas pelo cadastro manual e pela importação.
 */
final class RegrasProduto
{
    /**
     * @return array<string, list<string>>
     */
    public static function regras(): array
    {
        return [
            'descricao'    => ['bail', 'required', 'string', 'max:255'],
            'ncm'          => ['bail', 'nullable', 'required_without:nbs', 'prohibits:nbs', 'digits:8'],
            'nbs'          => ['bail', 'nullable', 'required_without:ncm', 'digits:9'],
            'cst'          => ['bail', 'required', 'digits:3'],
            'c_class_trib' => ['bail', 'required', 'digits:6'],
            'preco'        => ['bail', 'required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            ...RegrasImpostoSeletivo::regras(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensagens(): array
    {
        return [
            'ncm.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'nbs.required_without' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
            'ncm.prohibits'        => 'Informe apenas o NCM ou apenas o NBS.',
            'preco.decimal'        => 'O preço deve ter no máximo 2 casas decimais.',
            'preco.gt'             => 'O preço deve ser maior que zero.',
            ...RegrasImpostoSeletivo::mensagens(),
        ];
    }
}
