<?php

declare(strict_types=1);

use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Services\Simulacao\CronogramaTransicaoService;

test('aplica o cronograma da transição sobre as alíquotas plenas', function (int $ano, ?array $esperado): void {
    $cronograma = new CronogramaTransicaoService((require __DIR__.'/../../config/transicao.php')['anos']);
    $plenas = new AliquotasNominais(cbs: '8.8', ibsEstadual: '8.85', ibsMunicipal: '8.85');

    $aliquotas = $cronograma->aliquotasDoAno($ano, $plenas);

    expect($aliquotas === null ? null : [$aliquotas->cbs, $aliquotas->ibsEstadual, $aliquotas->ibsMunicipal])->toBe($esperado);
})->with([
    '2026 usa as de referência'      => [2026, null],
    '2027: CBS −0,1 p.p. e IBS 0,1%' => [2027, ['8.7', '0.05', '0.05']],
    '2028'                           => [2028, ['8.7', '0.05', '0.05']],
    '2029: IBS a 10%'                => [2029, ['8.8', '0.885', '0.885']],
    '2030: IBS a 20%'                => [2030, ['8.8', '1.77', '1.77']],
    '2031: IBS a 30%'                => [2031, ['8.8', '2.655', '2.655']],
    '2032: IBS a 40%'                => [2032, ['8.8', '3.54', '3.54']],
    '2033: plenas'                   => [2033, ['8.8', '8.85', '8.85']],
]);

test('lista os anos em ordem e recusa ano fora do cronograma', function (): void {
    $cronograma = new CronogramaTransicaoService((require __DIR__.'/../../config/transicao.php')['anos']);

    expect($cronograma->anos())->toBe([2026, 2027, 2028, 2029, 2030, 2031, 2032, 2033]);
    expect(fn () => $cronograma->aliquotasDoAno(2034, new AliquotasNominais('8.8', '8.85', '8.85')))
        ->toThrow(InvalidArgumentException::class, 'Ano fora do cronograma da transição: 2034.');
});

test('CBS não fica negativa com alíquota plena muito baixa', function (): void {
    $cronograma = new CronogramaTransicaoService((require __DIR__.'/../../config/transicao.php')['anos']);

    expect($cronograma->aliquotasDoAno(2027, new AliquotasNominais('0.05', '1', '1'))->cbs)->toBe('0');
});
