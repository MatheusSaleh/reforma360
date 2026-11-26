<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');

    Http::fake([
        '*/dados-abertos/ncm/vigentes*'                  => Http::response(fixtureJson('calculadora/regular/ncm-vigentes.json')),
        '*/dados-abertos/nbs/lista*'                     => Http::response(fixtureJson('calculadora/regular/nbs-lista.json')),
        '*/dados-abertos/ncm?ncm=10063021*'              => Http::response(fixtureJson('calculadora/regular/ncm-10063021.json')),
        '*/dados-abertos/ncm?ncm=22030000*'              => Http::response(fixtureJson('calculadora/regular/ncm-22030000.json')),
        '*/dados-abertos/ncm?ncm=99999999*'              => Http::response(fixtureJson('calculadora/regular/ncm-404.json'), 404),
        '*/dados-abertos/nbs?nbs=122011200*'             => Http::response(fixtureJson('calculadora/regular/nbs-122011200.json')),
        '*/dados-abertos/situacoes-tributarias/cbs-ibs*' => Http::response(fixtureJson('calculadora/regular/situacoes-tributarias-cbs-ibs.json')),
    ]);
});

test('busca NCM pelo prefixo do código, com ou sem pontos', function (string $termo): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => $termo]))
        ->assertSuccessful()
        ->assertJsonCount(4)
        ->assertJsonPath('0', ['codigo' => '10063011', 'descricao' => 'Polido ou brunido'])
        ->assertJsonPath('3.codigo', '10063029');
})->with(['1006.30', '100630', ' 1006 30 ']);

test('busca pela descrição sem diferenciar acentos e maiúsculas', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => 'CHICORIA torrada']))
        ->assertSuccessful()
        ->assertJsonCount(1)
        ->assertJsonPath('0.codigo', '21013000');
});

test('remove os traços de nível da descrição', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => '0901']))
        ->assertJsonPath('0.descricao', 'Não descafeinado');
});

test('busca NBS por código e por descrição', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'nbs', 'q' => '1.2201.1']))
        ->assertJsonCount(3)
        ->assertJsonPath('1.codigo', '122011200');

    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'nbs', 'q' => 'pré-escola']))
        ->assertJsonCount(1)
        ->assertJsonPath('0.descricao', 'Serviços de pré-escola');
});

test('limita a quantidade de sugestões e devolve lista vazia sem resultado', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => '10']))
        ->assertJsonCount(10);

    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => 'inexistente']))
        ->assertSuccessful()
        ->assertExactJson([]);
});

test('guarda a lista e o índice em cache', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => '1006']));
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => 'arroz']));

    Http::assertSentCount(1);
});

test('valida o termo de busca', function (string $termo): void {
    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'ncm', 'q' => $termo]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');

    Http::assertNothingSent();
})->with(['', 'a', str_repeat('x', 101)]);

test('responde 404 para tipo de nomenclatura inexistente', function (): void {
    $this->getJson('/dados-abertos/nomenclaturas/cfop?q=1234')->assertNotFound();
});

test('mostra a hierarquia do NCM', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.detalhe', ['tipo' => 'ncm', 'codigo' => '10063021']))
        ->assertSuccessful()
        ->assertExactJson([
            'codigo' => '10063021',
            'niveis' => [
                'Cereais',
                'Arroz',
                'Arroz semibranqueado ou branqueado, mesmo polido ou brunido (glaciado*)',
                'Não parboilizado',
                'Polido ou brunido',
            ],
            'tributado_pelo_imposto_seletivo' => false,
        ]);
});

test('indica quando o NCM é tributado pelo Imposto Seletivo', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.detalhe', ['tipo' => 'ncm', 'codigo' => '22030000']))
        ->assertJsonPath('tributado_pelo_imposto_seletivo', true);
});

test('mostra a hierarquia do NBS', function (): void {
    $this->getJson(route('dados-abertos.nomenclaturas.detalhe', ['tipo' => 'nbs', 'codigo' => '122011200']))
        ->assertJsonPath('niveis', ['Serviços de educação básica', 'Serviços de educação infantil', 'Serviços de pré-escola']);
});

test('responde 404 para código inexistente ou com tamanho errado', function (string $tipo, string $codigo): void {
    $this->getJson(route('dados-abertos.nomenclaturas.detalhe', ['tipo' => $tipo, 'codigo' => $codigo]))->assertNotFound();
})->with([
    'NCM inexistente'   => ['ncm', '99999999'],
    'NCM curto'         => ['ncm', '1006'],
    'NBS com 8 dígitos' => ['nbs', '12201120'],
]);

test('lista CSTs com as classificações tributárias', function (): void {
    $this->getJson(route('dados-abertos.situacoes-tributarias'))
        ->assertSuccessful()
        ->assertJsonCount(2)
        ->assertJsonPath('1.codigo', '200')
        ->assertJsonPath('1.descricao', 'Alíquota reduzida')
        ->assertJsonPath('1.classificacoes.2', ['codigo' => '200003', 'descricao' => fixtureJson('calculadora/regular/situacoes-tributarias-cbs-ibs.json')[1]['classificacoesTributarias'][2]['descricao']]);
});

test('responde 503 quando a calculadora está fora', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    cache()->flush();

    $this->getJson(route('dados-abertos.nomenclaturas.busca', ['tipo' => 'nbs', 'q' => 'escola']))->assertStatus(503);
});
