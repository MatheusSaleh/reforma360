<?php

declare(strict_types=1);

use App\Rules\Cnpj;

test('valida os dígitos verificadores do CNPJ', function (string $cnpj, bool $valido): void {
    expect(Cnpj::valido($cnpj))->toBe($valido);
})->with([
    'válido'        => ['11222333000181', true],
    'outro válido'  => ['11444777000161', true],
    'dígito errado' => ['11222333000182', false],
    'todos iguais'  => ['11111111111111', false],
    'curto'         => ['1122233300018', false],
    'com máscara'   => ['11.222.333/0001-81', false],
]);

test('gera CNPJs válidos', function (): void {
    foreach (range(1, 50) as $_) {
        expect(Cnpj::valido(Cnpj::gerar()))->toBeTrue();
    }
});
