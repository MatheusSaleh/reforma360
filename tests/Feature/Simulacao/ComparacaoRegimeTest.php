<?php

declare(strict_types=1);

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\CalculadoraSimples;
use App\Integrations\Calculadora\Data\ItemSimples;
use App\Integrations\Calculadora\Data\OperacaoSimples;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Models\ComparacaoRegime;
use App\Models\Importacao;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_simples.url', 'http://calculadora-simples:8080/api');
    $this->usuario = User::factory()->create();
});

/**
 * Notebook (calculado nos dois), curso (serviço: não vai ao Simples), carne (erro no Simples)
 * e um item com erro no regime regular (fica de fora).
 */
function simulacaoParaComparar(array $atributos = []): Simulacao
{
    $simulacao = Simulacao::factory()->for(test()->usuario->empresa)->create([
        'status'      => StatusSimulacao::ConcluidaComErros, 'data_fato_gerador' => '2027-03-10',
        'total_itens' => 4, 'itens_processados' => 4, 'itens_com_erro' => 1,
        ...$atributos,
    ]);
    $calculado = ['estado' => EstadoItemCalculo::CalculoSimulado];
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Notebook', 'ncm' => '84713012', 'nbs' => null, 'cst' => '000', 'c_class_trib' => '000001', 'base_calculo' => '3000.00', 'valor_cbs' => '261.00', 'valor_ibs' => '3.00', ...$calculado]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Curso', 'base_calculo' => '1200.00', 'valor_cbs' => '42.24', 'valor_ibs' => '0.48', ...$calculado]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Carne', 'ncm' => '02013000', 'nbs' => null, 'cst' => '200', 'c_class_trib' => '200035', 'base_calculo' => '80.00', 'valor_cbs' => '1.00', 'valor_ibs' => '0.01', ...$calculado]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Cerveja', 'ncm' => '22030000', 'nbs' => null, 'estado' => EstadoItemCalculo::InconsistenciaEntrada]);

    return $simulacao;
}

function fakeSimples(): void
{
    $resposta = fixtureJson('calculadora/simples/observabilidade-resposta.json');

    Http::fake([
        '*/simples-nacional/atividades*'                 => Http::response(fixtureJson('calculadora/simples/atividades.json')),
        '*/calculadora/observabilidade/simples-nacional' => function (Request $request) use ($resposta) {
            // Notebook → objeto 1 (calculado); carne → objeto 3 (VSN-047).
            $objetos = array_map(fn (array $item): array => ['nObj' => $item['numero']] + ($item['ncm'] === '02013000' ? $resposta['objetos'][2] : $resposta['objetos'][0]), $request['itens']);

            return Http::response(['objetos' => $objetos]);
        },
    ]);
}

$parametros = ['atividade' => '01', 'aliquota_cbs_simples' => '2,75', 'aliquota_ibs_simples' => '0,5', 'compras' => '1.000,00'];

test('o formulário lista só as atividades de mercadorias, sem alíquotas pré-preenchidas', function (): void {
    fakeSimples();
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao))->assertSee('Comparar com o Simples Nacional');

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.create', $simulacao))
        ->assertSuccessful()
        ->assertSee('01 · Revenda de mercadorias')
        ->assertSee('90 · Operações não Tributáveis')
        ->assertDontSee('Prestação de serviços de comunicação')
        ->assertSee('Use as do PGDAS ou peça ao seu contador')
        ->assertSee('placeholder="Ex.: 2,75"', escape: false);
});

test('compara os itens calculados, abate o crédito estimado das compras e mostra o resultado', function () use ($parametros): void {
    fakeSimples();
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $simulacao), $parametros)
        ->assertRedirect(route('simulacoes.comparacao.show', $simulacao));

    $comparacao = ComparacaoRegime::query()->sole();

    // Só o notebook foi calculado nos dois regimes.
    expect($comparacao)
        ->itens_comparados->toBe(1)
        ->itens_nao_comparados->toBe(2)
        ->base_calculo->toBe('3000.00')
        ->simples_cbs->toBe('82.50')
        ->simples_ibs->toBe('15.00')
        ->regular_cbs->toBe('261.00')
        ->regular_ibs->toBe('3.00')
        // 1.000,00 × (8,8 + 0,05 + 0,05)% = 89,00
        ->credito_estimado->toBe('89.00');
    expect($comparacao->totalSimples())->toBe('97.50')
        ->and($comparacao->liquidoRegular())->toBe('175.00')
        ->and($comparacao->diferenca())->toBe('77.50');

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $r): bool => str_ends_with($r->url(), '/calculadora/observabilidade/simples-nacional')
        && $r['data'] === '2027-03-10' && $r['tpDoc'] === '55' && $r['CRT'] === '1'
        && count($r['itens']) === 2
        && $r['itens'][0] === ['numero' => 1, 'cAtivSN' => '01', 'cClassTrib' => '000001', 'ncm' => '84713012', 'baseCalculo' => '3000.00', 'valorImpostoSeletivo' => '0.00', 'pIBSSN' => '0.5', 'pCBSSN' => '2.75']);

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.show', $simulacao))
        ->assertSuccessful()
        ->assertSeeInOrder(['Simples Nacional', 'R$ 97,50', 'Regime regular', 'R$ 175,00', 'crédito estimado R$ 89,00', 'Diferença', 'R$ 77,50', 'a menos no', 'Simples Nacional'], escape: false)
        ->assertSee('Não comparado: Serviços (NBS) ainda não são calculados')
        ->assertSee('Não comparado: VSN-047')
        ->assertDontSee('Cerveja')
        ->assertSee('Simulação simplificada');
});

test('sem compras, o regime regular aparece sem crédito e com aviso', function () use ($parametros): void {
    fakeSimples();
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $simulacao), [...$parametros, 'compras' => '']);

    $comparacao = ComparacaoRegime::query()->sole();
    expect($comparacao->compras)->toBeNull()
        ->and($comparacao->credito_estimado)->toBe('0.00')
        ->and($comparacao->liquidoRegular())->toBe('264.00');

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.show', $simulacao))
        ->assertSee('Sem o valor das compras');
});

test('crédito maior que os débitos zera o regime regular e avisa do saldo credor', function () use ($parametros): void {
    fakeSimples();
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $simulacao), [...$parametros, 'compras' => '10000']);

    $comparacao = ComparacaoRegime::query()->sole();
    expect($comparacao->liquidoRegular())->toBe('0.00')
        ->and($comparacao->sobraCredito())->toBeTrue()
        ->and($comparacao->diferenca())->toBe('-97.50');

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.show', $simulacao))
        ->assertSee('sobraria saldo credor')
        ->assertSeeInOrder(['a menos no', 'regime regular'], escape: false);
});

test('usa NFC-e para notas de consumidor e refazer substitui a comparação', function () use ($parametros): void {
    fakeSimples();
    $nfce = Importacao::factory()->for($this->usuario->empresa)->concluida()->create(['tipo_documento' => TipoDocumentoFiscal::Nfce]);
    $simulacao = simulacaoParaComparar(['importacao_id' => $nfce->id]);

    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $simulacao), $parametros);
    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $simulacao), [...$parametros, 'aliquota_cbs_simples' => '3']);

    expect(ComparacaoRegime::query()->count())->toBe(1)
        ->and(ComparacaoRegime::query()->sole()->simples_cbs)->toBe('82.50');
    Http::assertSent(fn (Request $r): bool => str_contains($r->url(), 'simples-nacional') && ($r['tpDoc'] ?? null) === '65');
});

test('explica quando a simulação não pode ser comparada', function (array $atributos, string $mensagem) use ($parametros): void {
    $simulacao = simulacaoParaComparar($atributos);

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.create', $simulacao))
        ->assertSee($mensagem);
    $this->actingAs($this->usuario)->from(route('simulacoes.comparacao.create', $simulacao))
        ->post(route('simulacoes.comparacao.store', $simulacao), $parametros)
        ->assertSessionHasErrors(['comparacao' => $mensagem]);

    Http::assertNothingSent();
})->with([
    '2026'           => [['data_fato_gerador' => '2026-06-10'], 'A calculadora do Simples Nacional da Receita só calcula operações a partir de 2027. Simule o catálogo com uma data a partir de 2027 para comparar.'],
    'em andamento'   => [['status' => StatusSimulacao::Processando], 'Aguarde a simulação terminar para comparar.'],
    'sem calculados' => [['itens_com_erro' => 4], 'A simulação não tem itens calculados para comparar.'],
]);

test('valida os dados do Simples', function (array $dados, string $campo) use ($parametros): void {
    fakeSimples();
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->from(route('simulacoes.comparacao.create', $simulacao))
        ->post(route('simulacoes.comparacao.store', $simulacao), [...$parametros, ...$dados])
        ->assertSessionHasErrors($campo);
    expect(ComparacaoRegime::query()->count())->toBe(0);
})->with([
    'sem atividade'     => [['atividade' => ''], 'atividade'],
    'sem CBS'           => [['aliquota_cbs_simples' => ''], 'aliquota_cbs_simples'],
    'IBS acima de 100'  => [['aliquota_ibs_simples' => '150'], 'aliquota_ibs_simples'],
    'compras negativas' => [['compras' => '-5'], 'compras'],
]);

test('mostra a indisponibilidade da calculadora do Simples', function () use ($parametros): void {
    Http::fake(['*' => Http::failedConnection()]);
    $simulacao = simulacaoParaComparar();

    $this->actingAs($this->usuario)->followingRedirects()->from(route('simulacoes.show', $simulacao))
        ->post(route('simulacoes.comparacao.store', $simulacao), $parametros)
        ->assertSee('A calculadora da Receita está indisponível no momento.');
});

test('não compara nem mostra simulação de outra empresa', function () use ($parametros): void {
    $alheia = Simulacao::factory()->create(['status' => StatusSimulacao::Concluida, 'data_fato_gerador' => '2027-03-10']);

    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.create', $alheia))->assertNotFound();
    $this->actingAs($this->usuario)->post(route('simulacoes.comparacao.store', $alheia), $parametros)->assertNotFound();
    $this->actingAs($this->usuario)->get(route('simulacoes.comparacao.show', $alheia))->assertNotFound();
});

test('o client do Simples lê o resultado por item e a recusa da operação', function (): void {
    Http::fake(['*/calculadora/observabilidade/simples-nacional' => Http::sequence()
        ->push(fixtureJson('calculadora/simples/observabilidade-resposta.json'))
        ->push(fixtureJson('calculadora/simples/observabilidade-422-data-2026.json'), 422),
    ]);
    $operacao = new OperacaoSimples('op', CarbonImmutable::parse('2027-03-10'), [
        new ItemSimples(1, '01', '000001', '3000.00', '2.75', '0.5', ncm: '84713012'),
    ]);

    $itens = app(CalculadoraSimples::class)->calcular($operacao);

    expect($itens)->toHaveCount(3)
        ->and($itens[0]->foiCalculado())->toBeTrue()
        ->and($itens[0]->valorCbs)->toBe('82.50')
        ->and($itens[0]->valorIbs)->toBe('15.00')
        ->and($itens[0]->total)->toBe('97.50')
        ->and($itens[1]->foiCalculado())->toBeFalse()
        ->and($itens[1]->erros[0]->codigo)->toBe('VSN-022');

    expect(fn () => app(CalculadoraSimples::class)->calcular($operacao))
        ->toThrow(PayloadInvalidoException::class);
});
