<?php

declare(strict_types=1);

use App\Support\Formatador;

test('formata moeda em pt-BR sem float', function (string $valor, string $esperado): void {
    expect(Formatador::moeda($valor))->toBe($esperado);
})->with([
    ['42.24', 'R$ 42,24'],
    ['1465.00', 'R$ 1.465,00'],
    ['1234567.89', 'R$ 1.234.567,89'],
    ['0.5', 'R$ 0,50'],
    ['0', 'R$ 0,00'],
    ['100', 'R$ 100,00'],
]);

test('formata percentual mantendo as casas decimais', function (string $valor, string $esperado): void {
    expect(Formatador::percentual($valor))->toBe($esperado);
})->with([
    ['8.80', '8,80%'],
    ['0.05', '0,05%'],
    ['60.00', '60,00%'],
    ['100', '100%'],
]);
