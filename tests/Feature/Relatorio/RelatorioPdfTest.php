<?php

declare(strict_types=1);

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Models\Empresa;
use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use App\Services\Simulacao\RelatorioSimulacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');

    Http::fake([
        '*/class-trib/200028*'     => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-200028.json')),
        '*/class-trib/*'           => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-404.json'), 404),
        '*/fundamentacoes-legais*' => Http::response(fixtureJson('calculadora/regular/fundamentacoes-legais.json')),
    ]);

    $empresa = Empresa::factory()->create(['razao_social' => 'Padaria Exemplo Ltda', 'cnpj' => '11222333000181']);
    $this->usuario = User::factory()->for($empresa)->create();
});

function simulacaoParaRelatorio(array $atributos = []): Simulacao
{
    $simulacao = Simulacao::factory()->for(test()->usuario->empresa)->create([
        'status'             => StatusSimulacao::ConcluidaComErros, 'data_fato_gerador' => '2027-03-10',
        'total_itens'        => 3, 'itens_processados' => 3, 'itens_com_erro' => 1,
        'total_base_calculo' => '4200.00', 'total_cbs' => '303.24', 'total_ibs' => '3.48', 'total_tributos' => '306.72',
        ...$atributos,
    ]);
    SimulacaoItem::factory()->for($simulacao)->create([
        'descricao' => 'Curso de idiomas', 'base_calculo' => '1200.00', 'estado' => EstadoItemCalculo::CalculoSimulado,
        'valor_cbs' => '42.24', 'valor_ibs' => '0.48', 'total_tributos' => '42.72', 'percentual_reducao' => '60.0000',
    ]);
    SimulacaoItem::factory()->for($simulacao)->create([
        'descricao' => 'Notebook', 'ncm' => '84713012', 'nbs' => null, 'cst' => '000', 'c_class_trib' => '000001', 'base_calculo' => '3000.00',
        'estado'    => EstadoItemCalculo::CalculoSimulado, 'valor_cbs' => '261.00', 'valor_ibs' => '3.00', 'total_tributos' => '264.00',
    ]);
    SimulacaoItem::factory()->for($simulacao)->create([
        'descricao' => 'Cerveja', 'ncm' => '22030000', 'nbs' => null, 'estado' => EstadoItemCalculo::InconsistenciaEntrada,
        'erros'     => [['codigo' => 'REG-013', 'mensagem' => 'Dados do Imposto Seletivo devem ser informados']],
    ]);

    return $simulacao;
}

function htmlDoRelatorio(Simulacao $simulacao): string
{
    return view('relatorios.simulacao', ['relatorio' => app(RelatorioSimulacaoService::class)->montar($simulacao)])->render();
}

test('baixa o PDF da simulação', function (): void {
    $simulacao = simulacaoParaRelatorio();

    $this->actingAs($this->usuario)->get(route('simulacoes.show', $simulacao))->assertSee('Baixar relatório (PDF)');

    $resposta = $this->actingAs($this->usuario)->get(route('simulacoes.relatorio', $simulacao));

    $resposta->assertSuccessful()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload("relatorio-simulacao-{$simulacao->id}.pdf");
    expect(mb_substr((string) $resposta->getContent(), 0, 5))->toBe('%PDF-');
});

test('o relatório tem cabeçalho, aviso de simulação, resumo, itens com redução e erros', function (): void {
    $html = htmlDoRelatorio(simulacaoParaRelatorio());

    expect($html)
        ->toContain('Padaria Exemplo Ltda')
        ->toContain('CNPJ 11.222.333/0001-81')
        ->toContain('Fato gerador em 10/03/2027')
        ->toContain('Alíquotas nominais informadas: CBS 8,8%, IBS estadual 0,05%, IBS municipal 0,05%')
        ->toContain('<strong>CALCULO_SIMULADO</strong>')
        ->toContain('não substitui a orientação de um contador')
        ->toContain('2 de 3')
        ->toContain('R$ 306,72')
        ->toContain('1 item não pôde ser simulado')
        ->toContain('Curso de idiomas')
        ->toContain('60%')
        ->toContain('REG-013: Dados do Imposto Seletivo devem ser informados');
});

test('agrupa por classificação tributária com tratamento e fundamentação legal', function (): void {
    $html = htmlDoRelatorio(simulacaoParaRelatorio());

    expect($html)
        ->toContain('000 / 000001')
        ->toContain('200 / 200028')
        ->toContain('Alíquota reduzida em 60%')
        ->toContain('Fornecimento dos serviços de educação (Anexo II)')
        ->toContain('cClassTrib 200028</strong>&nbsp;— Art. 129')
        ->toContain('Art. 129. Ficam reduzidas em 60%')
        ->toContain('cClassTrib 000001</strong>&nbsp;— LC 214/2025');
});

test('inclui a comparação com o Simples e a linha do tempo quando existem', function (): void {
    $simulacao = simulacaoParaRelatorio();
    $this->usuario->empresa->comparacoesRegime()->create([
        'simulacao_id'         => $simulacao->id, 'atividade' => '01',
        'aliquota_cbs_simples' => '2.75', 'aliquota_ibs_simples' => '0.5', 'compras' => '1000.00',
        'itens_comparados'     => 1, 'itens_nao_comparados' => 1, 'base_calculo' => '3000.00',
        'simples_cbs'          => '82.50', 'simples_ibs' => '15.00', 'regular_cbs' => '261.00', 'regular_ibs' => '3.00', 'credito_estimado' => '89.00',
    ]);
    $linha = LinhaDoTempo::factory()->create(['empresa_id' => $simulacao->empresa_id, 'simulacao_id' => $simulacao->id, 'status' => StatusSimulacao::Concluida]);
    Simulacao::factory()->for($this->usuario->empresa)->create([
        'linha_do_tempo_id'  => $linha->id, 'status' => StatusSimulacao::Concluida, 'data_fato_gerador' => '2033-03-10',
        'aliquota_cbs'       => '8.8', 'aliquota_ibs_estadual' => '8.85', 'aliquota_ibs_municipal' => '8.85',
        'total_base_calculo' => '3000.00', 'total_cbs' => '264.00', 'total_ibs' => '531.00',
    ]);

    $html = htmlDoRelatorio($simulacao);

    expect($html)
        ->toContain('Simples Nacional × regime regular')
        ->toContain('R$ 97,50')
        ->toContain('R$ 89,00')
        ->toContain('a menos no Simples')
        ->toContain('Linha do tempo 2026 → 2033')
        ->toContain('alíquotas plenas <strong>estimadas</strong>')
        ->toContain('CBS 8,8% · IBS 17,7%')
        ->toContain('R$ 795,00')
        ->toContain('26,5%');
});

test('sem linha do tempo concluída nem comparação, as seções não aparecem', function (): void {
    $simulacao = simulacaoParaRelatorio(['data_fato_gerador' => '2026-06-10', 'aliquota_cbs' => null, 'aliquota_ibs_estadual' => null, 'aliquota_ibs_municipal' => null]);
    LinhaDoTempo::factory()->create(['empresa_id' => $simulacao->empresa_id, 'simulacao_id' => $simulacao->id, 'status' => StatusSimulacao::Processando]);

    $html = htmlDoRelatorio($simulacao);

    expect($html)
        ->toContain('Alíquotas de referência da Receita para 2026')
        ->not->toContain('Linha do tempo 2026')
        ->not->toContain('Simples Nacional ×')
        ->not->toContain('<strong>CALCULO_SIMULADO</strong>');
});

test('acima do limite, detalha os itens de maior carga', function (): void {
    config()->set('simulacao.relatorio_maximo_itens', 1);

    $html = htmlDoRelatorio(simulacaoParaRelatorio());

    expect($html)
        ->toContain('Itens (os 1 de maior carga)')
        ->toContain('A simulação tem 3 itens')
        ->toContain('<td>84713012</td>')
        ->not->toContain('<td>122011200</td>');
});

test('gera o relatório sem fundamentação quando os dados abertos estão fora', function (): void {
    Http::swap(new Factory);
    Http::preventStrayRequests();
    Http::fake(['*' => Http::failedConnection()]);

    $html = htmlDoRelatorio(simulacaoParaRelatorio());

    expect($html)
        ->toContain('a fundamentação legal não foi incluída')
        ->toContain('Curso de idiomas');
});

test('recusa simulação em andamento e de outra empresa', function (): void {
    $emAndamento = simulacaoParaRelatorio(['status' => StatusSimulacao::Processando]);
    $alheia = Simulacao::factory()->create(['status' => StatusSimulacao::Concluida]);

    $this->actingAs($this->usuario)->from(route('simulacoes.show', $emAndamento))
        ->get(route('simulacoes.relatorio', $emAndamento))
        ->assertRedirect(route('simulacoes.show', $emAndamento))
        ->assertSessionHasErrors(['relatorio' => 'Aguarde a simulação terminar para gerar o relatório.']);

    $this->actingAs($this->usuario)->get(route('simulacoes.relatorio', $alheia))->assertNotFound();
});
