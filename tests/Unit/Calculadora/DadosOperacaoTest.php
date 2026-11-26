<?php

declare(strict_types=1);

use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Integrations\Calculadora\Data\ItemOperacao;
use App\Integrations\Calculadora\Data\Operacao;
use Carbon\CarbonImmutable;

test('recusa decimal fora do formato esperado', function (string $valor): void {
    expect(fn () => new ItemOperacao(numero: 1, cst: '000', cClassTrib: '000001', baseCalculo: $valor))
        ->toThrow(InvalidArgumentException::class, 'baseCalculo');
})->with([
    'vírgula'            => '100,00',
    'notação científica' => '1.0E+2',
    'negativo'           => '-10.00',
    'vazio'              => '',
]);

test('aceita decimais com ponto', function (string $valor): void {
    expect((new AliquotasNominais(cbs: $valor, ibsEstadual: '0', ibsMunicipal: '0.05'))->cbs)->toBe($valor);
})->with(['8.8', '8.80', '100', '0.0001']);

test('recusa item com NCM e NBS ao mesmo tempo', function (): void {
    new ItemOperacao(numero: 1, cst: '000', cClassTrib: '000001', ncm: '10063021', nbs: '122011200');
})->throws(InvalidArgumentException::class, 'NCM ou NBS');

test('recusa operação sem itens', function (): void {
    new Operacao(id: 'x', dhFatoGerador: CarbonImmutable::now(), municipio: 4314902, uf: 'RS', itens: []);
})->throws(InvalidArgumentException::class, 'pelo menos um item');

test('omite do payload os campos não informados', function (): void {
    $item = new ItemOperacao(numero: 7, cst: '000', cClassTrib: '000001', baseCalculo: '10.00', ncm: '22030000');

    expect($item->toPayload())->toBe([
        'numero'      => 7,
        'ncm'         => '22030000',
        'cst'         => '000',
        'cClassTrib'  => '000001',
        'baseCalculo' => '10.00',
    ]);
});
