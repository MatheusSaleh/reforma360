<?php

declare(strict_types=1);

test('as URLs das calculadoras estão configuradas', function (string $chave): void {
    expect(config("services.{$chave}.url"))
        ->toBeString()
        ->toStartWith('http');
})->with([
    'regime regular'   => 'calculadora_regular',
    'simples nacional' => 'calculadora_simples',
    'danfse'           => 'danfse',
]);
