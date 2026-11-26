<?php

declare(strict_types=1);

use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\GeradorDanfse;
use App\Integrations\Calculadora\Contracts\XmlTributacao;
use App\Integrations\Calculadora\Data\ItemOperacao;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\DanfseRejeitadoException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
    config()->set('services.danfse.url', 'http://danfse:8080/api');
});

function operacaoParaXml(): Operacao
{
    return new Operacao('op-xml', CarbonImmutable::parse('2026-06-10'), 4314902, 'RS', [
        new ItemOperacao(numero: 1, cst: '200', cClassTrib: '200003', baseCalculo: '100.00', ncm: '10063021'),
    ]);
}

test('gera o grupo enviando a resposta do cálculo normal ao /xml/generate', function (): void {
    $resultado = (string) file_get_contents(base_path('tests/Fixtures/calculadora/regular/regime-geral-resposta.json'));
    $grupo = (string) file_get_contents(base_path('tests/Fixtures/calculadora/regular/xml-generate-nfe.xml'));

    Http::fake([
        '*/calculadora/regime-geral'  => Http::response($resultado, 200, ['Content-Type' => 'application/json']),
        '*/calculadora/xml/generate*' => Http::response($grupo, 200, ['Content-Type' => 'application/xml']),
    ]);

    expect(app(XmlTributacao::class)->gerarGrupo(operacaoParaXml(), TipoDocumentoFiscal::Nfce))->toBe($grupo);

    Http::assertSent(fn (Request $r): bool => $r->url() === 'http://calculadora-regular:8080/api/calculadora/regime-geral'
        && $r['id'] === 'op-xml');
    Http::assertSent(fn (Request $r): bool => $r->url() === 'http://calculadora-regular:8080/api/calculadora/xml/generate?tipo=nfce'
        && $r->body() === $resultado);
});

test('repassa a recusa do cálculo como PayloadInvalidoException', function (): void {
    Http::fake(['*/calculadora/regime-geral' => Http::response(fixtureJson('calculadora/regular/regime-geral-422.json'), 422)]);

    expect(fn () => app(XmlTributacao::class)->gerarGrupo(operacaoParaXml(), TipoDocumentoFiscal::Nfe))
        ->toThrow(PayloadInvalidoException::class);

    Http::assertSentCount(1);
});

test('valida o XML no XSD', function (): void {
    Http::fake(['*/calculadora/xml/validate*' => Http::sequence()
        ->push('true', 200)
        ->push(fixtureJson('calculadora/regular/xml-validate-422.json'), 422),
    ]);

    $valido = app(XmlTributacao::class)->validar('<infNFe/>', TipoDocumentoFiscal::Nfe);
    $invalido = app(XmlTributacao::class)->validar('<IBSCBS/>', TipoDocumentoFiscal::Nfe);

    expect($valido->valido)->toBeTrue()
        ->and($invalido->valido)->toBeFalse()
        ->and($invalido->mensagem)->toContain('linha 1, coluna 9');
    Http::assertSent(fn (Request $r): bool => str_ends_with($r->url(), '/calculadora/xml/validate?tipo=nfe&subtipo=grupo')
        && $r->hasHeader('Content-Type', 'application/xml') && $r->body() === '<infNFe/>');
});

test('gera o DANFSe repassando o XML e devolvendo o PDF', function (): void {
    Http::fake(['danfse:8080/api/danfse' => Http::response('%PDF-1.7 conteudo', 200, ['Content-Type' => 'application/pdf'])]);

    expect(app(GeradorDanfse::class)->gerar('<NFSe/>'))->toBe('%PDF-1.7 conteudo');
    Http::assertSent(fn (Request $r): bool => $r->body() === '<NFSe/>' && $r->hasHeader('Content-Type', 'application/xml'));
});

test('erros do gerador de DANFSe', function (): void {
    Http::fake(['*' => Http::response(fixtureJson('calculadora/danfse/danfse-400.json'), 400)]);
    expect(fn () => app(GeradorDanfse::class)->gerar('<NFe/>'))
        ->toThrow(DanfseRejeitadoException::class, 'expected element type <NFSe> but have <NFe>');
});

test('DANFSe indisponível', function (): void {
    Http::fake(['*' => Http::failedConnection()]);

    try {
        app(GeradorDanfse::class)->gerar('<NFSe/>');
        $this->fail('Deveria lançar exceção.');
    } catch (CalculadoraIndisponivelException $e) {
        expect($e->mensagemParaUsuario())->toBe('O gerador de DANFSe está indisponível no momento. Tente novamente em instantes.');
    }
});
