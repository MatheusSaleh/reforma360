<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API pública (Sanctum)
    |--------------------------------------------------------------------------
    |
    | Permissões (abilities) que um token pode ter e limites de requisição por token.
    |
    */

    'permissoes' => [
        'produtos:ler'      => 'Ler produtos',
        'produtos:escrever' => 'Criar, alterar e excluir produtos',
        'simulacoes'        => 'Simular itens e o catálogo',
    ],

    'limite_por_minuto' => (int) env('API_LIMITE_POR_MINUTO', 60),

    'limite_simulacoes_por_minuto' => (int) env('API_LIMITE_SIMULACOES_POR_MINUTO', 20),

];
