<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Cronograma da transição da Reforma Tributária do Consumo (2026 → 2033)
|--------------------------------------------------------------------------
|
| Base legal: EC 132/2023 (ADCT, arts. 125 a 128) e LC 214/2025.
|
| - 2026: ano de teste. CBS 0,9% e IBS 0,1% — a calculadora da Receita usa as
|   alíquotas de referência dela; o app não envia alíquotas nominais.
| - 2027 e 2028: CBS integral, reduzida em 0,1 ponto percentual; IBS de 0,1%
|   (0,05% estadual + 0,05% municipal).
| - 2029 a 2032: CBS integral; IBS a 10%, 20%, 30% e 40% da alíquota plena
|   (enquanto ICMS e ISS caem na mesma proporção).
| - 2033: CBS e IBS integrais; ICMS e ISS extintos.
|
| As alíquotas PLENAS ainda não foram fixadas: os valores abaixo são ESTIMATIVAS
| editáveis pelo usuário (CBS 8,8% + IBS 17,7% ≈ 26,5%, estimativa do Ministério
| da Fazenda). A divisão do IBS entre estadual e municipal não altera o total.
|
*/

return [

    'aliquotas_plenas_estimadas' => [
        'cbs'           => env('TRANSICAO_ALIQUOTA_PLENA_CBS', '8.8'),
        'ibs_estadual'  => env('TRANSICAO_ALIQUOTA_PLENA_IBS_ESTADUAL', '8.85'),
        'ibs_municipal' => env('TRANSICAO_ALIQUOTA_PLENA_IBS_MUNICIPAL', '8.85'),
    ],

    /*
    | Por ano:
    | - 'referencia' => true: a calculadora usa as próprias alíquotas de referência.
    | - 'cbs_reducao_pp': pontos percentuais subtraídos da CBS plena.
    | - 'ibs_fixo': alíquotas fixas de IBS (estadual, municipal).
    | - 'ibs_fracao': fração da alíquota plena de IBS.
    */

    'anos' => [
        2026 => ['referencia' => true],
        2027 => ['cbs_reducao_pp' => '0.1', 'ibs_fixo' => ['estadual' => '0.05', 'municipal' => '0.05']],
        2028 => ['cbs_reducao_pp' => '0.1', 'ibs_fixo' => ['estadual' => '0.05', 'municipal' => '0.05']],
        2029 => ['ibs_fracao' => '0.1'],
        2030 => ['ibs_fracao' => '0.2'],
        2031 => ['ibs_fracao' => '0.3'],
        2032 => ['ibs_fracao' => '0.4'],
        2033 => ['ibs_fracao' => '1'],
    ],

];
