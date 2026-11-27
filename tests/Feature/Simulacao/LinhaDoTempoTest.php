<?php

declare(strict_types=1);

use App\Actions\Simulacao\FinalizarLinhaDoTempo;
use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use App\Notifications\LinhaDoTempoConcluida;
use App\Notifications\SimulacaoConcluida;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
    $this->usuario = User::factory()->create();
});

function simulacaoDeOrigem(array $atributos = []): Simulacao
{
    $simulacao = Simulacao::factory()->for(test()->usuario->empresa)->create([
        'user_id'           => test()->usuario->id,
        'status'            => StatusSimulacao::Concluida,
        'data_fato_gerador' => '2027-03-10',
        'total_itens'       => 2, 'itens_processados' => 2,
        ...$atributos,
    ]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Curso', 'base_calculo' => '1200.00', 'estado' => EstadoItemCalculo::CalculoSimulado]);
    SimulacaoItem::factory()->for($simulacao)->create(['descricao' => 'Notebook', 'nbs' => null, 'ncm' => '84713012', 'cst' => '000', 'c_class_trib' => '000001', 'base_calculo' => '3000.00', 'estado' => EstadoItemCalculo::CalculoSimulado]);

    return $simulacao;
}

$plenas = ['aliquota_plena_cbs' => '8,8', 'aliquota_plena_ibs_estadual' => '8,85', 'aliquota_plena_ibs_municipal' => '8,85'];

test('mostra o formulário com as alíquotas plenas estimadas', function (): void {
    $simulacao = simulacaoDeOrigem();

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao))->assertSee('Gerar linha do tempo 2026→2033');
    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', $simulacao))->assertRedirect(route('simulacoes.linha-do-tempo.create', $simulacao));

    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.create', $simulacao))
        ->assertSuccessful()
        ->assertSeeInOrder(['name="aliquota_plena_cbs"', 'value="8.8"', 'name="aliquota_plena_ibs_estadual"', 'value="8.85"'], escape: false)
        ->assertSee('estimativa do Ministério da Fazenda')
        ->assertSee('EC 132/2023');
});

test('cria uma simulação por ano com o cronograma da transição e os mesmos itens', function () use ($plenas): void {
    Bus::fake();
    $origem = simulacaoDeOrigem(['data_fato_gerador' => '2028-02-29']);

    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), $plenas)
        ->assertRedirect(route('simulacoes.linha-do-tempo.show', $origem));

    $linha = LinhaDoTempo::query()->sole();
    $anos = $linha->simulacoesDosAnos()->get();

    expect($linha->status)->toBe(StatusSimulacao::Processando)
        ->and($linha->aliquotasPlenas()->cbs)->toBe('8.8')
        ->and($anos)->toHaveCount(8)
        ->and($anos->map(fn ($s) => $s->data_fato_gerador->toDateString())->all())->toBe([
            '2026-02-28', '2027-02-28', '2028-02-29', '2029-02-28', '2030-02-28', '2031-02-28', '2032-02-29', '2033-02-28',
        ])
        ->and($anos->map(fn ($s) => [$s->aliquotasNominais()?->cbs, $s->aliquotasNominais()?->ibsEstadual])->all())->toBe([
            [null, null], ['8.7', '0.05'], ['8.7', '0.05'], ['8.8', '0.885'], ['8.8', '1.77'], ['8.8', '2.655'], ['8.8', '3.54'], ['8.8', '8.85'],
        ]);

    $ano2033 = $anos->last();
    expect($ano2033->total_itens)->toBe(2)
        ->and($ano2033->uf)->toBe($origem->uf)
        ->and($ano2033->itens()->orderBy('id')->pluck('origem_id')->all())->toBe($origem->itens()->orderBy('id')->pluck('id')->all())
        ->and($ano2033->itens()->orderBy('id')->pluck('base_calculo')->all())->toBe(['1200.00', '3000.00']);

    Bus::assertBatchCount(8);

    $this->actingAs($this->usuario)->get(route('simulacoes.index'))
        ->assertSee('#'.$origem->id.'<', escape: false)
        ->assertDontSee('#'.$ano2033->id.'<', escape: false);
});

test('recusa simulação em andamento ou que já é um ano de linha do tempo', function () use ($plenas): void {
    Bus::fake();
    $emAndamento = simulacaoDeOrigem(['status' => StatusSimulacao::Processando]);
    $ano = Simulacao::factory()->for($this->usuario->empresa)->create([
        'status'            => StatusSimulacao::Concluida,
        'linha_do_tempo_id' => LinhaDoTempo::factory()->create(['empresa_id' => $this->usuario->empresa_id])->id,
    ]);

    foreach ([$emAndamento, $ano] as $simulacao) {
        $this->actingAs($this->usuario)->from(route('simulacoes.linha-do-tempo.create', $simulacao))
            ->post(route('simulacoes.linha-do-tempo.store', $simulacao), $plenas)
            ->assertSessionHasErrors('linha_do_tempo');
    }

    Bus::assertNothingBatched();
});

test('valida as alíquotas plenas', function (array $dados, string $campo) use ($plenas): void {
    Bus::fake();
    $origem = simulacaoDeOrigem();

    $this->actingAs($this->usuario)->from(route('simulacoes.linha-do-tempo.create', $origem))
        ->post(route('simulacoes.linha-do-tempo.store', $origem), [...$plenas, ...$dados])
        ->assertSessionHasErrors($campo);
})->with([
    'vazia'        => [['aliquota_plena_cbs' => ''], 'aliquota_plena_cbs'],
    'acima de 100' => [['aliquota_plena_ibs_estadual' => '101'], 'aliquota_plena_ibs_estadual'],
    'negativa'     => [['aliquota_plena_ibs_municipal' => '-1'], 'aliquota_plena_ibs_municipal'],
]);

test('simula os 8 anos de ponta a ponta e avisa uma única vez', function () use ($plenas): void {
    Notification::fake();
    $calculado = fixtureJson('calculadora/regular/observabilidade-resposta-2027-servico-educacao.json')['objetos'][0];
    $calculado2026 = fixtureJson('calculadora/regular/observabilidade-resposta-2026-servico-educacao.json')['objetos'][0];
    Http::fake(['*/calculadora/observabilidade/regime-geral' => function (Request $request) use ($calculado, $calculado2026) {
        $modelo = str_starts_with($request['dhFatoGerador'], '2026') ? $calculado2026 : $calculado;

        return Http::response(['objetos' => array_map(fn (array $item) => ['nObj' => $item['numero']] + $modelo, $request['itens'])]);
    }]);
    $origem = simulacaoDeOrigem();

    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), $plenas);

    $linha = LinhaDoTempo::query()->sole();
    expect($linha->status)->toBe(StatusSimulacao::Concluida)
        ->and($linha->concluida_em)->not->toBeNull()
        ->and($linha->simulacoesDosAnos()->pluck('status')->unique()->all())->toBe([StatusSimulacao::Concluida]);

    Http::assertSentCount(8);
    Http::assertSent(fn (Request $r): bool => str_starts_with($r['dhFatoGerador'], '2026') && ! isset($r['itens'][0]['aliquotasNominais']));
    Http::assertSent(fn (Request $r): bool => str_starts_with($r['dhFatoGerador'], '2031')
        && $r['itens'][0]['aliquotasNominais'] === ['cbs' => '8.8', 'ibsEstadual' => '2.655', 'ibsMunicipal' => '2.655']);

    Notification::assertSentToTimes($this->usuario, LinhaDoTempoConcluida::class, 1);
    Notification::assertNotSentTo($this->usuario, SimulacaoConcluida::class);

    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', $origem))
        ->assertSuccessful()
        ->assertSee('<svg', escape: false)
        ->assertSee('Total da simulação — Tributos por ano')
        ->assertSeeInOrder(['2026', 'referência da Receita', 'R$ 8,64', '2027', 'CBS 8,7%', 'IBS 0,1%', 'R$ 84,48'])
        ->assertSee('CBS 8,8% · IBS 17,7%')
        ->assertDontSee('data-progresso-simulacao', escape: false);
});

test('mostra a linha do tempo de um item escolhido', function (): void {
    $origem = simulacaoDeOrigem();
    $linha = LinhaDoTempo::factory()->create(['empresa_id' => $this->usuario->empresa_id, 'simulacao_id' => $origem->id, 'status' => StatusSimulacao::ConcluidaComErros]);
    $curso = $origem->itens()->where('descricao', 'Curso')->sole();

    foreach ([2026 => ['4.32', '0.48'], 2027 => null] as $ano => $valores) {
        $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create([
            'linha_do_tempo_id' => $linha->id, 'status' => StatusSimulacao::ConcluidaComErros, 'data_fato_gerador' => "{$ano}-03-10",
            'aliquota_cbs'      => $ano === 2026 ? null : '8.7', 'aliquota_ibs_estadual' => $ano === 2026 ? null : '0.05', 'aliquota_ibs_municipal' => $ano === 2026 ? null : '0.05',
        ]);
        SimulacaoItem::factory()->for($simulacao)->create($valores === null
            ? ['origem_id' => $curso->id, 'estado' => EstadoItemCalculo::InconsistenciaEntrada, 'erros' => [['codigo' => 'REG-013', 'mensagem' => 'Dados do Imposto Seletivo devem ser informados']]]
            : ['origem_id' => $curso->id, 'estado' => EstadoItemCalculo::Calculado, 'valor_cbs' => $valores[0], 'valor_ibs' => $valores[1]]);
    }

    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', [$origem, 'item' => $curso->id]))
        ->assertSuccessful()
        ->assertSee('Curso — Tributos por ano')
        ->assertSeeInOrder(['2026', 'R$ 1.200,00', 'R$ 4,32', 'R$ 0,48', 'R$ 4,80', '0,40%'])
        ->assertSee('Dados do Imposto Seletivo devem ser informados');
});

test('acompanha o progresso pelos canais dos 8 anos enquanto processa', function () use ($plenas): void {
    Bus::fake();
    $origem = simulacaoDeOrigem();
    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), $plenas);
    $ids = LinhaDoTempo::query()->sole()->simulacoesDosAnos()->pluck('id');

    $resposta = $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', $origem));

    $resposta->assertSee('Simulando os 8 anos')
        ->assertSee('0 de 16');
    foreach ($ids as $id) {
        $resposta->assertSee("simulacoes.{$id}");
    }
});

test('refazer substitui a linha do tempo anterior', function () use ($plenas): void {
    Bus::fake();
    $origem = simulacaoDeOrigem();

    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), $plenas);
    $primeira = LinhaDoTempo::query()->sole();
    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), [...$plenas, 'aliquota_plena_cbs' => '9']);

    expect(LinhaDoTempo::query()->count())->toBe(1)
        ->and(LinhaDoTempo::query()->sole()->aliquotasPlenas()->cbs)->toBe('9')
        ->and(LinhaDoTempo::query()->find($primeira->id))->toBeNull()
        ->and(Simulacao::query()->whereNotNull('linha_do_tempo_id')->count())->toBe(8);
});

test('a finalização avisa uma vez mesmo se chamada duas vezes', function (): void {
    Notification::fake();
    $origem = simulacaoDeOrigem();
    $linha = LinhaDoTempo::factory()->create(['empresa_id' => $this->usuario->empresa_id, 'simulacao_id' => $origem->id, 'user_id' => $this->usuario->id]);
    Simulacao::factory()->for($this->usuario->empresa)->create(['linha_do_tempo_id' => $linha->id, 'status' => StatusSimulacao::Concluida]);
    Simulacao::factory()->for($this->usuario->empresa)->create(['linha_do_tempo_id' => $linha->id, 'status' => StatusSimulacao::ConcluidaComErros]);

    app(FinalizarLinhaDoTempo::class)->handle($linha->id);
    app(FinalizarLinhaDoTempo::class)->handle($linha->id);

    expect($linha->fresh()->status)->toBe(StatusSimulacao::ConcluidaComErros);
    Notification::assertSentToTimes($this->usuario, LinhaDoTempoConcluida::class, 1);
});

test('não finaliza enquanto algum ano estiver em andamento', function (): void {
    Notification::fake();
    $linha = LinhaDoTempo::factory()->create(['empresa_id' => $this->usuario->empresa_id, 'user_id' => $this->usuario->id]);
    Simulacao::factory()->for($this->usuario->empresa)->create(['linha_do_tempo_id' => $linha->id, 'status' => StatusSimulacao::Concluida]);
    Simulacao::factory()->for($this->usuario->empresa)->create(['linha_do_tempo_id' => $linha->id, 'status' => StatusSimulacao::Processando]);

    app(FinalizarLinhaDoTempo::class)->handle($linha->id);

    expect($linha->fresh()->status)->toBe(StatusSimulacao::Processando);
    Notification::assertNothingSent();
});

test('abrir a linha do tempo marca o aviso como lido', function (): void {
    $origem = simulacaoDeOrigem();
    $linha = LinhaDoTempo::factory()->create(['empresa_id' => $this->usuario->empresa_id, 'simulacao_id' => $origem->id, 'status' => StatusSimulacao::Concluida]);
    $this->usuario->notify(new LinhaDoTempoConcluida($linha));

    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', $origem))->assertSuccessful();

    expect($this->usuario->unreadNotifications()->count())->toBe(0);
});

test('não acessa a linha do tempo de outra empresa', function () use ($plenas): void {
    $alheia = Simulacao::factory()->create(['status' => StatusSimulacao::Concluida]);

    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.create', $alheia))->assertNotFound();
    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $alheia), $plenas)->assertNotFound();
    $this->actingAs($this->usuario)->get(route('simulacoes.linha-do-tempo.show', $alheia))->assertNotFound();
});

test('simula o IS apenas nas vigências aplicáveis e mostra o valor na linha do tempo do item', function () use ($plenas): void {
    $origem = simulacaoDeOrigem();
    $item = $origem->itens()->where('descricao', 'Curso')->sole();
    $item->update([
        'descricao'    => 'Cerveja', 'ncm' => '22030000', 'nbs' => null, 'cst' => '000', 'c_class_trib' => '000001',
        'base_calculo' => '150.00', 'is_aliquota_ad_valorem' => '10.00',
    ]);
    $cerveja = fixtureJson('calculadora/regular/observabilidade-resposta.json')['objetos'][1];
    $semIs = fixtureJson('calculadora/regular/observabilidade-resposta-2026-servico-educacao.json')['objetos'][0];

    Http::fake([
        '*/dados-abertos/ncm?ncm=22030000&data=*'    => Http::response(fixtureJson('calculadora/regular/ncm-22030000.json')),
        '*/calculadora/observabilidade/regime-geral' => fn (Request $request) => Http::response([
            'objetos' => array_map(fn (array $i) => ['nObj' => $i['numero']] + (isset($i['impostoSeletivo']) ? $cerveja : $semIs), $request['itens']),
        ]),
    ]);

    $this->actingAs($this->usuario)->post(route('simulacoes.linha-do-tempo.store', $origem), $plenas)->assertRedirect();

    $simulacoes = LinhaDoTempo::query()->sole()->simulacoesDosAnos()->get()->keyBy(fn (Simulacao $simulacao) => $simulacao->data_fato_gerador->year);
    expect($simulacoes[2026]->itens()->where('origem_id', $item->id)->sole()->valor_imposto_seletivo)->toBeNull()
        ->and($simulacoes[2027]->total_imposto_seletivo)->toBe('15.00')
        ->and($simulacoes[2027]->itens()->where('origem_id', $item->id)->sole()->total_tributos)->toBe('29.68');

    Http::assertSent(fn (Request $r): bool => str_ends_with($r->url(), '/regime-geral') && str_starts_with($r['dhFatoGerador'], '2027')
        && $r['itens'][0]['impostoSeletivo']['baseCalculo'] === '150.00'
        && $r['itens'][0]['aliquotasNominais']['impostoSeletivo']['adValorem'] === '10.0000');
    Http::assertNotSent(fn (Request $r): bool => str_ends_with($r->url(), '/regime-geral')
        && str_starts_with($r['dhFatoGerador'], '2026') && isset($r['itens'][0]['impostoSeletivo']));

    $this->get(route('simulacoes.linha-do-tempo.show', [$origem, 'item' => $item->id]))
        ->assertSuccessful()
        ->assertSeeInOrder(['2027', 'R$ 14,52', 'R$ 0,16', 'R$ 15,00', 'R$ 29,68']);
});
