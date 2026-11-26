<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Alíquotas nominais
    |--------------------------------------------------------------------------
    |
    | A partir deste ano a calculadora exige as alíquotas nominais na entrada
    | (antes disso elas são proibidas e a calculadora usa as de referência).
    |
    */

    'ano_inicio_aliquotas_nominais' => 2027,

    /*
    | Valores sugeridos no formulário, em %. São estimativas editáveis pelo
    | usuário (os mesmos do exemplo oficial da Receita), não alíquotas legais.
    */

    'aliquotas_nominais_sugeridas' => [
        'cbs'           => env('SIMULACAO_ALIQUOTA_CBS', '8.8'),
        'ibs_estadual'  => env('SIMULACAO_ALIQUOTA_IBS_ESTADUAL', '0.05'),
        'ibs_municipal' => env('SIMULACAO_ALIQUOTA_IBS_MUNICIPAL', '0.05'),
    ],

    /*
    | Período aceito no formulário (transição da reforma).
    */

    'data_minima' => '2026-01-01',
    'data_maxima' => '2033-12-31',

    /*
    | Simulação em lote: itens enviados em cada chamada à calculadora (um job por bloco).
    */

    'itens_por_chamada' => (int) env('SIMULACAO_ITENS_POR_CHAMADA', 50),

];
