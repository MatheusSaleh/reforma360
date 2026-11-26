<?php

declare(strict_types=1);

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Enums\TipoDocumentoFiscal;
use App\Models\Importacao;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
    $this->usuario = User::factory()->create();
});

function simulacaoConcluida(array $atributos = [], int $calculados = 2, int $comErro = 1, string $nbs = ''): Simulacao
{
    $simulacao = Simulacao::factory()->for(test()->usuario->empresa)->create([
        'status'         => $comErro > 0 ? StatusSimulacao::ConcluidaComErros : StatusSimulacao::Concluida,
        'total_itens'    => $calculados + $comErro, 'itens_processados' => $calculados + $comErro, 'itens_com_erro' => $comErro,
        'total_tributos' => '1.00',
        ...$atributos,
    ]);

    SimulacaoItem::factory()->count($calculados)->for($simulacao)->sequence(fn ($s) => ['descricao' => "Item {$s->index}"])->create([
        'ncm'          => '10063021', 'nbs' => $nbs === '' ? null : $nbs, 'cst' => '200', 'c_class_trib' => '200003',
        'base_calculo' => '100.00', 'estado' => EstadoItemCalculo::CalculoSimulado,
    ]);
    SimulacaoItem::factory()->count($comErro)->for($simulacao)->create(['ncm' => '02013000', 'nbs' => null, 'estado' => EstadoItemCalculo::InconsistenciaEntrada]);

    return $simulacao;
}

function fakeXmlDaCalculadora(bool $valido = true): void
{
    Http::fake([
        '*/calculadora/regime-geral'  => Http::response(fixtureJson('calculadora/regular/regime-geral-resposta.json')),
        '*/calculadora/xml/generate*' => Http::response((string) file_get_contents(base_path('tests/Fixtures/calculadora/regular/xml-generate-nfe.xml')), 200, ['Content-Type' => 'application/xml']),
        '*/calculadora/xml/validate*' => $valido ? Http::response('true') : Http::response(fixtureJson('calculadora/regular/xml-validate-422.json'), 422),
    ]);
}

test('baixa o XML validado só com os itens calculados', function (): void {
    fakeXmlDaCalculadora();
    $simulacao = simulacaoConcluida();

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao))
        ->assertSee('Baixar XML do grupo IBS/CBS (NF-e)');

    $resposta = $this->actingAs($this->usuario)->get(route('simulacoes.xml-ibscbs', $simulacao));

    $resposta->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertDownload("simulacao-{$simulacao->id}-grupo-ibscbs-nfe.xml");
    expect($resposta->getContent())->toContain('<infNFe xmlns="http://www.portalfiscal.inf.br/nfe">');

    Http::assertSent(fn (Request $r): bool => str_ends_with($r->url(), '/calculadora/regime-geral')
        && count($r['itens']) === 2
        && $r['itens'][1]['numero'] === 2
        && $r['itens'][0]['aliquotasNominais'] === ['cbs' => '8.8', 'ibsEstadual' => '0.05', 'ibsMunicipal' => '0.05']);
    Http::assertSent(fn (Request $r): bool => str_ends_with($r->url(), '/calculadora/xml/validate?tipo=nfe&subtipo=grupo'));
});

test('usa o tipo da nota importada (NFC-e)', function (): void {
    fakeXmlDaCalculadora();
    $importacao = Importacao::factory()->for($this->usuario->empresa)->concluida()->create(['tipo_documento' => TipoDocumentoFiscal::Nfce, 'numero_documento' => '10']);
    $simulacao = simulacaoConcluida(['importacao_id' => $importacao->id]);

    $this->actingAs($this->usuario)->get(route('simulacoes.xml-ibscbs', $simulacao))
        ->assertDownload("simulacao-{$simulacao->id}-grupo-ibscbs-nfce.xml");
    Http::assertSent(fn (Request $r): bool => str_contains($r->url(), 'xml/generate?tipo=nfce'));
});

test('não oferece o XML para NFS-e nem para simulações com serviços', function (): void {
    $nfse = Importacao::factory()->for($this->usuario->empresa)->concluida()->create(['tipo_documento' => TipoDocumentoFiscal::Nfse]);
    $daNota = simulacaoConcluida(['importacao_id' => $nfse->id]);
    $comServicos = simulacaoConcluida(nbs: '122011200');

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $daNota))
        ->assertDontSee('Baixar XML do grupo')
        ->assertSee('ainda não gera o XML do grupo IBS/CBS para NFS-e');
    $this->actingAs($this->usuario)->get(route('simulacoes.show', $comServicos))
        ->assertSee('A simulação tem serviços (NBS)');

    $this->actingAs($this->usuario)->from(route('simulacoes.show', $daNota))
        ->get(route('simulacoes.xml-ibscbs', $daNota))
        ->assertRedirect(route('simulacoes.show', $daNota))
        ->assertSessionHasErrors('xml');
    Http::assertNothingSent();
});

test('mostra o erro quando o XML não passa no XSD', function (): void {
    fakeXmlDaCalculadora(valido: false);
    $simulacao = simulacaoConcluida();

    $this->actingAs($this->usuario)->from(route('simulacoes.show', $simulacao))
        ->get(route('simulacoes.xml-ibscbs', $simulacao))
        ->assertRedirect(route('simulacoes.show', $simulacao))
        ->assertSessionHasErrors(['xml' => 'O XML gerado não passou na validação do XSD da Receita: Erro na validação do XML: Erro na linha 1, coluna 9: cvc-elt.1.a: Cannot find the declaration of element \'IBSCBS\'.']);
});

test('recusa simulação em andamento, sem itens calculados ou acima do limite da NF-e', function (Closure $preparar, string $mensagem): void {
    $simulacao = $preparar();

    $this->actingAs($this->usuario)->from(route('simulacoes.index'))
        ->get(route('simulacoes.xml-ibscbs', $simulacao))
        ->assertSessionHasErrors(['xml' => $mensagem]);
    Http::assertNothingSent();
})->with([
    'em andamento'    => [fn () => simulacaoConcluida(['status' => StatusSimulacao::Processando]), 'Aguarde a simulação terminar para gerar o XML.'],
    'sem calculados'  => [fn () => simulacaoConcluida(['status' => StatusSimulacao::Falhou], calculados: 0), 'A simulação não tem itens calculados para gerar o XML.'],
    'acima do limite' => [function () {
        config()->set('simulacao.maximo_itens_xml', 1);

        return simulacaoConcluida();
    }, 'Uma NF-e aceita até 1 itens; a simulação tem 2 itens calculados.'],
]);

test('não gera o XML de simulação de outra empresa', function (): void {
    $alheia = Simulacao::factory()->create(['status' => StatusSimulacao::Concluida]);

    $this->actingAs($this->usuario)->get(route('simulacoes.xml-ibscbs', $alheia))->assertNotFound();
});

test('mostra na tela quando a calculadora está fora ao gerar o XML', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    $simulacao = simulacaoConcluida();

    $this->actingAs($this->usuario)->followingRedirects()->from(route('simulacoes.show', $simulacao))
        ->get(route('simulacoes.xml-ibscbs', $simulacao))
        ->assertSee('A calculadora da Receita está indisponível no momento.');
});
