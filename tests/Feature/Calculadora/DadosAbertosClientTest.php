<?php

declare(strict_types=1);

use App\Enums\Uf;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
});

test('consulta a classificação tributária pela data e guarda em cache', function (): void {
    Http::fake(['*/class-trib/200028*' => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-200028.json'))]);
    $data = CarbonImmutable::parse('2027-03-10');

    $classificacao = app(DadosAbertos::class)->classificacaoTributaria('200028', $data);
    app(DadosAbertos::class)->classificacaoTributaria('200028', $data);

    expect($classificacao->descricao)->toBe('Fornecimento dos serviços de educação (Anexo II)')
        ->and($classificacao->tratamentoTributario)->toBe('Alíquota reduzida em 60%')
        ->and($classificacao->nomenclatura)->toBe('NBS');
    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'http://calculadora-regular:8080/api/calculadora/dados-abertos/classificacoes-tributarias/cbs-ibs/class-trib/200028?data=2027-03-10');
});

test('devolve null para classificação inexistente', function (): void {
    Http::fake(['*' => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-404.json'), 404)]);

    expect(app(DadosAbertos::class)->classificacaoTributaria('999999', CarbonImmutable::parse('2027-03-10')))->toBeNull();
});

test('encontra a fundamentação legal do cClassTrib e converte as quebras de linha', function (): void {
    Http::fake(['*/fundamentacoes-legais*' => Http::response(fixtureJson('calculadora/regular/fundamentacoes-legais.json'))]);

    $fundamentacao = app(DadosAbertos::class)->fundamentacaoLegal('200028', CarbonImmutable::parse('2027-03-10'));

    expect($fundamentacao->texto)->toStartWith('Art. 129.')
        ->and($fundamentacao->situacaoTributaria)->toBe('Alíquota reduzida')
        ->and($fundamentacao->referenciaNormativa)->toContain("\n")
        ->and($fundamentacao->referenciaNormativa)->not->toContain('\\n')
        ->and(app(DadosAbertos::class)->fundamentacaoLegal('123456', CarbonImmutable::parse('2027-03-10')))->toBeNull();

    Http::assertSentCount(1);
});

test('lista os municípios da UF', function (): void {
    Http::fake(['*/ufs/municipios?siglaUf=RS' => Http::response(fixtureJson('calculadora/regular/municipios-rs.json'))]);

    $municipios = app(DadosAbertos::class)->municipios(Uf::RS);

    expect($municipios)->toHaveCount(3)
        ->and($municipios[2]->codigo)->toBe(4314902)
        ->and($municipios[2]->nome)->toBe('Porto Alegre');
});
