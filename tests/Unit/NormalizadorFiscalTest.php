<?php

declare(strict_types=1);

use App\Importers\ColunasCatalogo;
use App\Support\NormalizadorFiscal;

test('converte decimais pt-BR para ponto', function (string $entrada, string $esperado): void {
    expect(NormalizadorFiscal::decimal($entrada))->toBe($esperado);
})->with([
    ['1.234,56', '1234.56'],
    ['R$ 1.234,56', '1234.56'],
    ['28,9', '28.9'],
    ['1234.56', '1234.56'],
    ['8,8%', '8.8'],
]);

test('restaura zeros à esquerda de códigos numéricos', function (string $entrada, int $tamanho, string $esperado): void {
    expect(NormalizadorFiscal::codigo($entrada, $tamanho))->toBe($esperado);
})->with([
    'CST 0'        => ['0', 3, '000'],
    'cClassTrib 1' => ['1', 6, '000001'],
    'já completo'  => ['200003', 6, '200003'],
    'com pontos'   => ['1006.30.21', 8, '10063021'],
    'vazio'        => ['', 3, ''],
]);

test('reconhece variações do cabeçalho', function (string $cabecalho, ?string $coluna): void {
    expect(ColunasCatalogo::canonica($cabecalho))->toBe($coluna);
})->with([
    ['descricao', 'descricao'],
    ['cclasstrib', 'c_class_trib'],
    ['preco_r', 'preco'],
    ['codigo_ncm', 'ncm'],
    ['coluna_qualquer', null],
]);
