<?php

declare(strict_types=1);

use App\Support\GraficoColunas;

test('escolhe um teto redondo e ticks redondos', function (string $maximo, string $teto, array $ticks): void {
    $grafico = GraficoColunas::montar([['rotulo' => 'A', 'valores' => [$maximo, '0']]], 200);

    expect($grafico['teto'])->toBe($teto)
        ->and($grafico['ticks'])->toBe($ticks);
})->with([
    ['42.72', '50', ['0.00', '10.00', '20.00', '30.00', '40.00', '50.00']],
    ['150', '200', ['0.00', '50.00', '100.00', '150.00', '200.00']],
    ['1000', '1000', ['0.00', '200.00', '400.00', '600.00', '800.00', '1000.00']],
    ['0', '1', ['0.00', '0.20', '0.40', '0.60', '0.80', '1.00']],
]);

test('calcula alturas inteiras proporcionais e trata coluna sem valor', function (): void {
    $grafico = GraficoColunas::montar([
        ['rotulo' => '2027', 'valores' => ['30.00', '10.00']],
        ['rotulo' => '2028', 'valores' => [null, null]],
    ], 200);

    expect($grafico['colunas'][0]['alturas'])->toBe([120, 40])
        ->and($grafico['colunas'][0]['total'])->toBe('40.00')
        ->and($grafico['colunas'][1]['alturas'])->toBe([0, 0])
        ->and($grafico['colunas'][1]['total'])->toBeNull();
});
