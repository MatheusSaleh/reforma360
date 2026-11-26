<?php

declare(strict_types=1);

/*
| Tradução parcial: as regras usadas no app. Chaves ausentes caem no fallback (en).
*/

return [
    'after_or_equal'  => ':Attribute deve ser uma data igual ou posterior a :date.',
    'before_or_equal' => ':Attribute deve ser uma data igual ou anterior a :date.',
    'confirmed'       => 'A confirmação de :attribute não confere.',
    'date_format'     => ':Attribute não está no formato :format.',
    'decimal'         => ':Attribute deve ter :decimal casas decimais.',
    'digits'          => ':Attribute deve ter :digits dígitos.',
    'email'           => 'Informe um e-mail válido.',
    'enum'            => ':Attribute selecionado é inválido.',
    'gt'              => [
        'numeric' => ':Attribute deve ser maior que :value.',
    ],
    'integer'   => ':Attribute deve ser um número inteiro.',
    'lowercase' => ':Attribute deve estar em minúsculas.',
    'max'       => [
        'numeric' => ':Attribute deve ser no máximo :max.',
        'string'  => ':Attribute deve ter no máximo :max caracteres.',
    ],
    'min' => [
        'numeric' => ':Attribute deve ser no mínimo :min.',
        'string'  => ':Attribute deve ter no mínimo :min caracteres.',
    ],
    'numeric'  => ':Attribute deve ser um número.',
    'password' => [
        'letters'       => ':Attribute deve conter pelo menos uma letra.',
        'mixed'         => ':Attribute deve conter letras maiúsculas e minúsculas.',
        'numbers'       => ':Attribute deve conter pelo menos um número.',
        'symbols'       => ':Attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'Esta senha apareceu em um vazamento de dados. Escolha outra.',
    ],
    'prohibits'        => 'Preencha :attribute ou :other, não os dois.',
    'required'         => 'Informe :attribute.',
    'required_without' => 'Informe :attribute ou :values.',
    'string'           => ':Attribute deve ser um texto.',
    'unique'           => 'Já existe um cadastro com este :attribute.',

    'attributes' => [
        'c_class_trib'          => 'cClassTrib',
        'cnpj'                  => 'CNPJ',
        'cst'                   => 'CST',
        'descricao'             => 'descrição',
        'email'                 => 'e-mail',
        'name'                  => 'nome',
        'nbs'                   => 'NBS',
        'ncm'                   => 'NCM',
        'password'              => 'senha',
        'password_confirmation' => 'confirmação da senha',
        'preco'                 => 'preço',
        'razao_social'          => 'razão social',
    ],
];
