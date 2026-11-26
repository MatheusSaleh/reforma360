<?php

declare(strict_types=1);

use App\Actions\Simulacao\FinalizarSimulacao;
use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Events\SimulacaoAtualizada;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Jobs\SimularItensJob;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use App\Notifications\SimulacaoConcluida;
use App\Services\Simulacao\SimularLoteService;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Broadcasting\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
    config()->set('simulacao.itens_por_chamada', 2);

    $this->usuario = User::factory()->create();
});

/**
 * @return array<string, string>
 */
function parametrosDoLote(array $sobrescrever = []): array
{
    return array_merge([
        'data'                   => '2027-03-10',
        'uf'                     => 'RS',
        'municipio'              => '4314902',
        'aliquota_cbs'           => '8,8',
        'aliquota_ibs_estadual'  => '0,05',
        'aliquota_ibs_municipal' => '0,05',
    ], $sobrescrever);
}

/**
 * Calculadora falsa: responde a cada item enviado com o resultado real gravado (serviço de
 * educação, CBS 42,24 + IBS 0,48) ou, para o NCM 02013000, com o erro real REG-011.
 */
function fakeCalculadoraDoLote(): void
{
    $calculado = fixtureJson('calculadora/regular/observabilidade-resposta-2027-servico-educacao.json')['objetos'][0];
    $comErro = fixtureJson('calculadora/regular/observabilidade-resposta-todos-com-erro.json')['objetos'][0];

    Http::fake(['*/calculadora/observabilidade/regime-geral' => function (Request $request) use ($calculado, $comErro) {
        $objetos = array_map(
            fn (array $item): array => ['nObj' => $item['numero']] + (($item['ncm'] ?? null) === '02013000' ? $comErro : $calculado),
            $request['itens'],
        );

        return Http::response(['objetos' => $objetos], 200, ['x-warning-dados-simulados' => '6']);
    }]);
}

test('mostra o formulário com a quantidade de produtos ou o estado vazio', function (): void {
    $this->actingAs($this->usuario)->get(route('simulacoes.create'))
        ->assertSuccessful()
        ->assertSee('Você ainda não tem produtos');

    Produto::factory()->count(3)->for($this->usuario->empresa)->create();

    $this->actingAs($this->usuario)->get(route('simulacoes.create'))
        ->assertSee('Simular 3 produtos')
        ->assertSee('name="aliquota_cbs"', escape: false);
});

test('cria a simulação com um retrato do catálogo e despacha o lote em blocos', function (): void {
    Bus::fake();
    $produtos = Produto::factory()->count(5)->for($this->usuario->empresa)->servico()->create();
    Produto::factory()->create();

    $this->actingAs($this->usuario)
        ->post(route('simulacoes.store'), parametrosDoLote())
        ->assertRedirect(route('simulacoes.show', Simulacao::query()->sole()));

    $simulacao = Simulacao::query()->sole();

    expect($simulacao)
        ->status->toBe(StatusSimulacao::Pendente)
        ->total_itens->toBe(5)
        ->user_id->toBe($this->usuario->id)
        ->aliquota_cbs->toBe('8.8000')
        ->batch_id->not->toBeNull();
    expect($simulacao->aliquotasNominais()->cbs)->toBe('8.8');
    expect($simulacao->itens()->pluck('produto_id')->sort()->values()->all())->toBe($produtos->pluck('id')->sort()->values()->all());
    expect($simulacao->itens()->first()->base_calculo)->toBe($produtos->first()->preco);

    Bus::assertBatched(fn (PendingBatch $batch): bool => $batch->name === "Simulação #{$simulacao->id}"
        && $batch->queue() === 'simulacoes'
        && $batch->allowsFailures()
        && $batch->jobs->count() === 3
        && $batch->jobs->every(fn ($job) => $job instanceof SimularItensJob)
        && $batch->jobs->sum(fn (SimularItensJob $job) => count($job->idsDosItens)) === 5);
});

test('não guarda alíquotas nominais antes de 2027', function (): void {
    Bus::fake();
    Produto::factory()->for($this->usuario->empresa)->create();

    $this->actingAs($this->usuario)->post(route('simulacoes.store'), parametrosDoLote(['data' => '2026-06-10']));

    expect(Simulacao::query()->sole()->aliquota_cbs)->toBeNull();
});

test('exige produtos cadastrados', function (): void {
    Bus::fake();

    $this->actingAs($this->usuario)
        ->from(route('simulacoes.create'))
        ->post(route('simulacoes.store'), parametrosDoLote())
        ->assertRedirect(route('simulacoes.create'))
        ->assertSessionHasErrors(['simulacao' => 'Cadastre ou importe produtos antes de simular o catálogo.']);

    Bus::assertNothingBatched();
});

test('valida os parâmetros', function (array $dados, string $campo): void {
    Bus::fake();
    Produto::factory()->for($this->usuario->empresa)->create();

    $this->actingAs($this->usuario)
        ->from(route('simulacoes.create'))
        ->post(route('simulacoes.store'), parametrosDoLote($dados))
        ->assertSessionHasErrors($campo);

    expect(Simulacao::query()->count())->toBe(0);
})->with([
    'sem UF'                   => [['uf' => ''], 'uf'],
    'município de outra UF'    => [['municipio' => '3550308'], 'municipio'],
    'data fora da transição'   => [['data' => '2034-01-01'], 'data'],
    'alíquota ausente em 2027' => [['aliquota_cbs' => ''], 'aliquota_cbs'],
]);

test('processa o lote inteiro, grava resultados por item, soma totais e avisa o usuário', function (): void {
    Notification::fake();
    Event::fake([SimulacaoAtualizada::class]);
    fakeCalculadoraDoLote();

    Produto::factory()->count(4)->for($this->usuario->empresa)->servico()
        ->sequence(fn ($s) => ['descricao' => "Curso {$s->index}", 'preco' => '1200.00'])->create();
    Produto::factory()->for($this->usuario->empresa)->mercadoria('02013000')->create(['descricao' => 'Carne', 'c_class_trib' => '200035']);

    $this->actingAs($this->usuario)->post(route('simulacoes.store'), parametrosDoLote());

    $simulacao = Simulacao::query()->sole();

    expect($simulacao)
        ->status->toBe(StatusSimulacao::ConcluidaComErros)
        ->total_itens->toBe(5)
        ->itens_processados->toBe(5)
        ->itens_com_erro->toBe(1)
        ->total_base_calculo->toBe('4800.00')
        ->total_cbs->toBe('168.96')
        ->total_ibs->toBe('1.92')
        ->total_tributos->toBe('170.88')
        ->concluida_em->not->toBeNull();

    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request): bool => $request['municipio'] === 4314902
        && $request['itens'][0]['aliquotasNominais'] === ['cbs' => '8.8', 'ibsEstadual' => '0.05', 'ibsMunicipal' => '0.05']
        && $request['itens'][0]['numero'] === 1);

    $curso = SimulacaoItem::query()->where('descricao', 'Curso 0')->sole();
    expect($curso)
        ->estado->toBe(EstadoItemCalculo::CalculoSimulado)
        ->valor_cbs->toBe('42.24')
        ->valor_ibs->toBe('0.48')
        ->total_tributos->toBe('42.72')
        ->percentual_reducao->toBe('60.0000');
    expect($curso->detalhes['cbs'])->toBe(['aliquota' => '8.80', 'reducao' => '60.00', 'aliquota_efetiva' => '3.52', 'valor' => '42.24'])
        ->and($curso->detalhes['memoria']['cbs'])->toContain('Art. 129');

    expect(SimulacaoItem::query()->where('descricao', 'Carne')->sole())
        ->estado->toBe(EstadoItemCalculo::InconsistenciaEntrada)
        ->erros->toBe([['codigo' => 'REG-011', 'mensagem' => 'NCM de código 02013000 não vinculada à Classificação Tributária de código 200035 (CBS e IBS)']]);

    Notification::assertSentTo($this->usuario, SimulacaoConcluida::class, function (SimulacaoConcluida $notificacao, array $canais) use ($simulacao): bool {
        return $canais === ['database', 'mail']
            && $notificacao->simulacao->is($simulacao)
            && $notificacao->toArray($this->usuario)['mensagem'] === "A simulação #{$simulacao->id} terminou: 4 itens simulados e 1 com erro.";
    });

    Event::assertDispatchedTimes(SimulacaoAtualizada::class, 4);
});

test('a simulação falha quando nenhum item é calculado', function (): void {
    Notification::fake();
    fakeCalculadoraDoLote();
    Produto::factory()->for($this->usuario->empresa)->mercadoria('02013000')->create();

    $this->actingAs($this->usuario)->post(route('simulacoes.store'), parametrosDoLote());

    expect(Simulacao::query()->sole())
        ->status->toBe(StatusSimulacao::Falhou)
        ->total_tributos->toBeNull();
});

test('marca o bloco inteiro com o erro quando a calculadora rejeita a operação', function (): void {
    Http::fake(['*' => Http::response(fixtureJson('calculadora/regular/observabilidade-422-sem-itens.json'), 422)]);
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['total_itens' => 2]);
    $itens = SimulacaoItem::factory()->count(2)->for($simulacao)->create();

    app(SimularLoteService::class)->processar($simulacao, $itens->pluck('id')->all());

    expect($simulacao->fresh())->itens_processados->toBe(2)->itens_com_erro->toBe(2)->status->toBe(StatusSimulacao::Processando);
    expect($itens->first()->fresh())
        ->estado->toBe(EstadoItemCalculo::InconsistenciaEntrada)
        ->erros->toBe([['codigo' => 'VAL-003', 'mensagem' => "Campo 'itens' must not be empty, recebido: '[]'"]]);
});

test('com a calculadora fora, deixa os itens pendentes para a nova tentativa', function (): void {
    Http::fake(['*' => Http::failedConnection()]);
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['total_itens' => 1]);
    $item = SimulacaoItem::factory()->for($simulacao)->create();

    expect(fn () => app(SimularLoteService::class)->processar($simulacao, [$item->id]))
        ->toThrow(CalculadoraIndisponivelException::class);

    expect($item->fresh()->estado)->toBeNull()
        ->and($simulacao->fresh()->itens_processados)->toBe(0);
});

test('não simula de novo itens já processados', function (): void {
    fakeCalculadoraDoLote();
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['total_itens' => 1]);
    $item = SimulacaoItem::factory()->for($simulacao)->create(['estado' => EstadoItemCalculo::Calculado, 'total_tributos' => '1.00']);

    app(SimularLoteService::class)->processar($simulacao, [$item->id]);

    Http::assertNothingSent();
});

test('a finalização marca como falha técnica os itens que ficaram sem resultado', function (): void {
    Notification::fake();
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['user_id' => $this->usuario->id, 'total_itens' => 2, 'status' => StatusSimulacao::Processando]);
    SimulacaoItem::factory()->for($simulacao)->create([
        'estado' => EstadoItemCalculo::CalculoSimulado, 'base_calculo' => '100.00', 'valor_cbs' => '8.80', 'valor_ibs' => '0.10', 'total_tributos' => '8.90',
    ]);
    $pendente = SimulacaoItem::factory()->for($simulacao)->create();

    app(FinalizarSimulacao::class)->handle($simulacao->id);

    expect($pendente->fresh())
        ->estado->toBe(EstadoItemCalculo::FalhaTecnica)
        ->erros->toBe([['codigo' => null, 'mensagem' => 'Não foi possível simular este item agora. Inicie uma nova simulação.']]);
    expect($simulacao->fresh())
        ->status->toBe(StatusSimulacao::ConcluidaComErros)
        ->itens_com_erro->toBe(1)
        ->total_tributos->toBe('8.90');
    Notification::assertSentTo($this->usuario, SimulacaoConcluida::class);
});

test('mostra o progresso enquanto processa e o resultado ao concluir', function (): void {
    $emAndamento = Simulacao::factory()->for($this->usuario->empresa)->create(['status' => StatusSimulacao::Processando, 'total_itens' => 10, 'itens_processados' => 4]);

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $emAndamento))
        ->assertSuccessful()
        ->assertSee('data-canal="simulacoes.'.$emAndamento->id.'"', escape: false)
        ->assertSee('aria-valuenow="40"', escape: false)
        ->assertSee('4 de 10');

    $concluida = Simulacao::factory()->for($this->usuario->empresa)->create([
        'status'             => StatusSimulacao::ConcluidaComErros, 'total_itens' => 2, 'itens_processados' => 2, 'itens_com_erro' => 1,
        'total_base_calculo' => '1200.00', 'total_cbs' => '42.24', 'total_ibs' => '0.48', 'total_tributos' => '42.72',
    ]);
    SimulacaoItem::factory()->for($concluida)->create(['descricao' => 'Curso ok', 'estado' => EstadoItemCalculo::CalculoSimulado, 'valor_cbs' => '42.24', 'valor_ibs' => '0.48', 'total_tributos' => '42.72', 'percentual_reducao' => '60.0000']);
    SimulacaoItem::factory()->for($concluida)->create(['descricao' => 'Item com erro', 'estado' => EstadoItemCalculo::InconsistenciaEntrada, 'erros' => [['codigo' => 'REG-011', 'mensagem' => 'NCM não vinculada']]]);

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $concluida))
        ->assertSee('CALCULO_SIMULADO')
        ->assertSee('R$ 42,72')
        ->assertSeeInOrder(['Curso ok', '60%', 'R$ 42,24'])
        ->assertSee('REG-011: NCM não vinculada')
        ->assertDontSee('data-progresso-simulacao', escape: false);

    $this->actingAs($this->usuario)->get(route('simulacoes.show', [$concluida, 'erros' => 1]))
        ->assertSee('Item com erro')
        ->assertDontSee('Curso ok');
});

test('abrir a simulação marca o aviso como lido e o menu mostra os não lidos', function (): void {
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['status' => StatusSimulacao::Concluida, 'user_id' => $this->usuario->id]);
    $this->usuario->notify(new SimulacaoConcluida($simulacao));

    $this->actingAs($this->usuario)->get(route('simulacoes.index'))->assertSee('1 aviso novo');

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao));

    expect($this->usuario->unreadNotifications()->count())->toBe(0);
    $this->actingAs($this->usuario)->get(route('simulacoes.index'))->assertDontSee('aviso novo');
});

test('o e-mail de conclusão tem o resumo, o total e o link', function (): void {
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create([
        'status' => StatusSimulacao::Concluida, 'total_itens' => 3, 'itens_processados' => 3, 'total_tributos' => '1234.56',
    ]);

    $html = (string) (new SimulacaoConcluida($simulacao))->toMail($this->usuario)->render();

    expect($html)->toContain("A simulação #{$simulacao->id} terminou: 3 itens simulados.")
        ->toContain('R$ 1.234,56')
        ->toContain(route('simulacoes.show', $simulacao))
        ->toContain('não substituem a orientação de um contador')
        ->toContain('Se o botão');
});

test('lista só as simulações da empresa e esconde as de outras (404)', function (): void {
    Simulacao::factory()->for($this->usuario->empresa)->create();
    $alheia = Simulacao::factory()->create();

    $this->actingAs($this->usuario)->get(route('simulacoes.index'))->assertSuccessful()->assertDontSee('#'.$alheia->id.'<');
    $this->actingAs($this->usuario)->get(route('simulacoes.show', $alheia))->assertNotFound();
});

test('só a empresa dona pode ouvir o canal de progresso', function (): void {
    config()->set('broadcasting.default', 'reverb');
    config()->set('broadcasting.connections.reverb.key', 'chave-teste');
    config()->set('broadcasting.connections.reverb.secret', 'segredo-teste');
    config()->set('broadcasting.connections.reverb.app_id', 'app-teste');
    app()->forgetInstance(BroadcastManager::class);
    app()->forgetInstance(Factory::class);
    require base_path('routes/channels.php');

    $minha = Simulacao::factory()->for($this->usuario->empresa)->create();
    $alheia = Simulacao::factory()->create();

    $this->actingAs($this->usuario)
        ->post('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => "private-simulacoes.{$minha->id}"])
        ->assertSuccessful()
        ->assertJsonStructure(['auth']);

    $this->actingAs($this->usuario)
        ->post('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => "private-simulacoes.{$alheia->id}"])
        ->assertForbidden();
});

test('o evento de progresso não expõe dados fiscais', function (): void {
    $simulacao = Simulacao::factory()->for($this->usuario->empresa)->create(['status' => StatusSimulacao::Processando, 'total_itens' => 4, 'itens_processados' => 1]);
    $evento = new SimulacaoAtualizada($simulacao);

    expect($evento->broadcastOn()->name)->toBe("private-simulacoes.{$simulacao->id}")
        ->and($evento->broadcastAs())->toBe('simulacao.atualizada')
        ->and(array_keys($evento->broadcastWith()))->toBe(['status', 'status_label', 'total', 'processados', 'com_erro', 'percentual', 'em_andamento'])
        ->and($evento->broadcastWith()['percentual'])->toBe(25);
});

test('visitante é levado ao login', function (): void {
    $this->get(route('simulacoes.index'))->assertRedirect(route('login'));
    $this->post(route('simulacoes.store'))->assertRedirect(route('login'));
});
