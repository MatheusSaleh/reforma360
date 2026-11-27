<?php

declare(strict_types=1);

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Models\Importacao;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');

    $this->usuario = User::factory()->create();
});

/**
 * @return array<string, string>
 */
function itemParaApi(array $sobrescrever = []): array
{
    return array_merge([
        'nbs'                    => '122011200',
        'cst'                    => '200',
        'c_class_trib'           => '200028',
        'valor'                  => '1200.00',
        'uf'                     => 'RS',
        'municipio'              => '4314902',
        'data'                   => '2027-03-10',
        'aliquota_cbs'           => '8.8',
        'aliquota_ibs_estadual'  => '0.05',
        'aliquota_ibs_municipal' => '0.05',
    ], $sobrescrever);
}

function fakeCalculadoraDaApi(): void
{
    Http::fake([
        '*/calculadora/observabilidade/regime-geral'                      => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta-2027-servico-educacao.json'), 200, ['x-warning-dados-simulados' => '6', 'X-CALC-DB-VERSION' => 'V0073']),
        '*/dados-abertos/classificacoes-tributarias/cbs-ibs/class-trib/*' => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-200028.json')),
        '*/dados-abertos/fundamentacoes-legais*'                          => Http::response(fixtureJson('calculadora/regular/fundamentacoes-legais.json')),
    ]);
}

test('simula um item e devolve o resultado marcado como simulação', function (): void {
    fakeCalculadoraDaApi();
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi())
        ->assertSuccessful()
        ->assertJsonPath('data.calculo_simulado', true)
        ->assertJsonPath('data.base_calculo', '1200.00')
        ->assertJsonPath('data.cbs.aliquota', '8.80')
        ->assertJsonPath('data.cbs.percentual_reducao', '60.00')
        ->assertJsonPath('data.cbs.valor', '42.24')
        ->assertJsonPath('data.total_tributos', '42.72')
        ->assertJsonPath('data.classificacao.codigo', '200028')
        ->assertJsonPath('data.versao_base_dados', 'V0073')
        ->assertJsonPath('data.aviso', fn (string $aviso): bool => str_contains($aviso, 'Simulação'));
});

test('simular um item exige a permissão simulacoes', function (): void {
    Sanctum::actingAs($this->usuario, ['produtos:ler']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi())->assertForbidden();

    Http::assertNothingSent();
});

test('valida a entrada do item', function (): void {
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi(['aliquota_cbs' => null, 'cst' => '2']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['aliquota_cbs', 'cst']);

    Http::assertNothingSent();
});

test('responde 503 em JSON quando a calculadora está fora', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi())
        ->assertStatus(503)
        ->assertJsonPath('message', 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.');
});

test('limita as simulações de item por token', function (): void {
    fakeCalculadoraDaApi();
    config()->set('api.limite_simulacoes_por_minuto', 1);
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi())->assertSuccessful();
    $this->postJson('/api/v1/simulacoes/item', itemParaApi())->assertTooManyRequests();
});

test('inicia a simulação do catálogo e responde 202', function (): void {
    Bus::fake();
    Produto::factory()->count(3)->for($this->usuario->empresa)->servico()->create();
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes', itemParaApi())
        ->assertAccepted()
        ->assertJsonPath('data.status', StatusSimulacao::Pendente->value)
        ->assertJsonPath('data.itens.total', 3)
        ->assertJsonPath('data.aliquotas_nominais.cbs', '8.8')
        ->assertJsonPath('data.calculo_simulado', true);

    expect(Simulacao::query()->sole()->user_id)->toBe($this->usuario->id);
    Bus::assertBatchCount(1);
});

test('não inicia a simulação sem produtos nem com nota de outra empresa', function (): void {
    Bus::fake();
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes', itemParaApi())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['simulacao' => 'Cadastre ou importe produtos antes de simular o catálogo.']);

    $notaAlheia = Importacao::factory()->create();

    $this->postJson('/api/v1/simulacoes', itemParaApi(['importacao_id' => $notaAlheia->id]))->assertNotFound();

    Bus::assertNothingBatched();
});

test('consulta a simulação e o resultado por item, só da própria empresa', function (): void {
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create([
        'status'         => StatusSimulacao::Concluida,
        'total_itens'    => 2,
        'itens_com_erro' => 1,
        'total_tributos' => '42.72',
    ]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'A - calculado', 'estado' => EstadoItemCalculo::CalculoSimulado, 'total_tributos' => '42.72']);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'B - com erro', 'estado' => EstadoItemCalculo::InconsistenciaEntrada, 'erros' => ['NCM não vinculado.']]);
    $alheia = Simulacao::factory()->create();
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->getJson('/api/v1/simulacoes')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $simulacao->id);

    $this->getJson("/api/v1/simulacoes/{$simulacao->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.status', StatusSimulacao::Concluida->value)
        ->assertJsonPath('data.em_andamento', false)
        ->assertJsonPath('data.totais.tributos', '42.72');

    $this->getJson("/api/v1/simulacoes/{$simulacao->id}/itens")
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.calculado', true)
        ->assertJsonPath('data.0.total_tributos', '42.72')
        ->assertJsonPath('data.1.erros', ['NCM não vinculado.'])
        ->assertJsonPath('aviso', fn (string $aviso): bool => str_contains($aviso, 'Simulação'));

    $this->getJson("/api/v1/simulacoes/{$simulacao->id}/itens?erros=1")
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.descricao', 'B - com erro');

    $this->getJson("/api/v1/simulacoes/{$alheia->id}")->assertNotFound();
    $this->getJson("/api/v1/simulacoes/{$alheia->id}/itens")->assertNotFound();
});

test('a documentação da API está publicada em /docs', function (): void {
    $this->get('/docs')
        ->assertSuccessful()
        ->assertSee('API do Reforma360')
        ->assertSee('/api/v1/simulacoes/item');
});

test('a API devolve o resultado do IS com alíquota ad rem e sem percentual', function (): void {
    $cerveja = fixtureJson('calculadora/regular/observabilidade-resposta.json')['objetos'][1];
    $cerveja['nObj'] = 1;
    $cerveja['tribCalc']['IS']['pISEspec'] = '1.25';
    unset($cerveja['tribCalc']['IS']['pIS']);
    Http::fake([
        '*/calculadora/observabilidade/regime-geral'                      => Http::response(['objetos' => [$cerveja]]),
        '*/dados-abertos/ncm?ncm=22030000*'                               => Http::response(fixtureJson('calculadora/regular/ncm-22030000.json')),
        '*/dados-abertos/classificacoes-tributarias/cbs-ibs/class-trib/*' => Http::response([], 404),
        '*/dados-abertos/fundamentacoes-legais*'                          => Http::response([]),
    ]);
    Sanctum::actingAs($this->usuario, ['simulacoes']);

    $this->postJson('/api/v1/simulacoes/item', itemParaApi([
        'nbs'                => '', 'ncm' => '22030000', 'cst' => '000', 'c_class_trib' => '000001', 'valor' => '150.00',
        'is_aliquota_ad_rem' => '1.25', 'is_quantidade' => '12', 'is_unidade' => 'LT',
    ]))->assertSuccessful()
        ->assertJsonPath('data.calculo_simulado', true)
        ->assertJsonPath('data.imposto_seletivo.aliquota', null)
        ->assertJsonPath('data.imposto_seletivo.aliquota_ad_rem', '1.25')
        ->assertJsonPath('data.imposto_seletivo.unidade', 'LT')
        ->assertJsonPath('data.imposto_seletivo.quantidade', '12')
        ->assertJsonPath('data.imposto_seletivo.valor', '15.00');
});
