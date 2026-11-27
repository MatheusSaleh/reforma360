<?php

declare(strict_types=1);

test('o README incorpora o GIF da demonstração', function (): void {
    $raiz = dirname(__DIR__, 2);
    $caminho = 'docs/assets/demonstracao.gif';

    expect((string) file_get_contents($raiz.'/README.md'))->toMatch('~!\[[^\]]+\]\('.preg_quote($caminho, '~').'\)~');
    expect($raiz.'/'.$caminho)->toBeFile();
    expect(getimagesize($raiz.'/'.$caminho)[2] ?? null)->toBe(IMAGETYPE_GIF);
});

test('o README referencia cada decisão de arquitetura registrada', function (string $caminho): void {
    $raiz = dirname(__DIR__, 2);

    expect((string) file_get_contents($raiz.'/README.md'))->toContain("]({$caminho})");
    expect($raiz.'/'.$caminho)->toBeFile();
})->with([
    'filas em lote' => 'docs/adr/0001-simulacoes-em-lote.md',
    'calculadoras'  => 'docs/adr/0002-calculadoras-sem-fork.md',
    'importadores'  => 'docs/adr/0003-importadores-strategy.md',
]);
