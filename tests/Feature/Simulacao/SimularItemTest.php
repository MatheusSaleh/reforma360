<?php

declare(strict_types=1);

use App\Services\Simulacao\ImpostoSeletivoDoItem;
use Dom\HTMLDocument;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
});

/**
 * @param  array<string, mixed>  $sobrescrever
 * @return array<string, mixed>
 */
function dadosDoFormulario(array $sobrescrever = []): array
{
    return array_merge([
        'nbs'                    => '1.2201.12.00',
        'cst'                    => '200',
        'c_class_trib'           => '200028',
        'valor'                  => '1.200,00',
        'uf'                     => 'RS',
        'municipio'              => '4314902',
        'data'                   => '2027-03-10',
        'aliquota_cbs'           => '8,8',
        'aliquota_ibs_estadual'  => '0,05',
        'aliquota_ibs_municipal' => '0,05',
    ], $sobrescrever);
}

/**
 * @param  array<array-key, mixed>|null  $resposta
 */
function fakeCalculadora(?array $resposta = null, int $status = 200, array $headers = []): void
{
    Http::fake([
        '*/calculadora/observabilidade/regime-geral' => Http::response(
            $resposta ?? fixtureJson('calculadora/regular/observabilidade-resposta-2027-servico-educacao.json'),
            $status,
            $headers,
        ),
        '*/dados-abertos/classificacoes-tributarias/cbs-ibs/class-trib/*' => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-200028.json')),
        '*/dados-abertos/fundamentacoes-legais*'                          => Http::response(fixtureJson('calculadora/regular/fundamentacoes-legais.json')),
    ]);
}

test('a página inicial redireciona para o formulário', function (): void {
    $this->get('/')->assertRedirect(route('simulacoes.item.create'));
});

test('exibe o formulário com as alíquotas sugeridas', function (): void {
    $this->get(route('simulacoes.item.create'))
        ->assertSuccessful()
        ->assertSee('Simular um item')
        ->assertSee('data-moeda', escape: false)
        ->assertSee('Digite 1000 para R$ 10,00.')
        ->assertSee('name="aliquota_cbs"', escape: false)
        ->assertSee('value="8.8"', escape: false)
        ->assertSee('estimativas editáveis');

    Http::assertNothingSent();
});

test('todos os campos do simulador apontam para uma dica explicativa', function (): void {
    $resposta = $this->get(route('simulacoes.item.create'))->assertSuccessful();
    $html = HTMLDocument::createFromString((string) $resposta->getContent(), LIBXML_NOERROR);
    $campos = $html->querySelectorAll('form[method="GET"] :is(input, select)');

    expect($campos->length)->toBe(19);

    foreach ($campos as $campo) {
        $descricoes = explode(' ', $campo->getAttribute('aria-describedby') ?? '');
        $dicas = array_filter($descricoes, fn (string $id): bool => $html->getElementById($id)?->getAttribute('role') === 'tooltip');

        expect($dicas)->toHaveCount(1);
        expect(mb_trim($html->getElementById(reset($dicas))?->textContent ?? ''))->not->toBeEmpty();
    }

    Http::assertNothingSent();
});

test('renderiza os campos de classificação com opções próprias e preserva os códigos informados', function (): void {
    $this->withSession(['_old_input' => ['cst' => '000', 'c_class_trib' => '000001']])
        ->get(route('simulacoes.item.create'))
        ->assertSuccessful()
        ->assertSee('name="cst" value="000"', escape: false)
        ->assertSee('name="c_class_trib" value="000001"', escape: false)
        ->assertSee('inputmode="numeric" data-digitos="3"', escape: false)
        ->assertSee('inputmode="numeric" data-digitos="6"', escape: false)
        ->assertSee('id="cst-opcoes" class="lista-opcoes" role="listbox"', escape: false)
        ->assertSee('id="c_class_trib-opcoes" class="lista-opcoes" role="listbox"', escape: false)
        ->assertSee('data-explicacoes-cst=', escape: false)
        ->assertSee('"000":"Aplica as al', escape: false)
        ->assertSee('"830":"Exclui valores', escape: false)
        ->assertDontSee('<datalist', escape: false);

    Http::assertNothingSent();
});

test('exibe a busca de UF e município com os estados ordenados pelo nome', function (): void {
    $this->withSession(['_old_input' => ['uf' => 'SP', 'municipio' => '3550308']])
        ->get(route('simulacoes.item.create'))
        ->assertSuccessful()
        ->assertSee('aria-controls="campo-uf-opcoes"', escape: false)
        ->assertSee('aria-controls="campo-municipio-opcoes"', escape: false)
        ->assertSee('data-selecionado="3550308"', escape: false)
        ->assertSeeInOrder(['Acre · AC', 'Alagoas · AL', 'Amapá · AP', 'Amazonas · AM', 'Bahia · BA', 'Pará · PA', 'Paraíba · PB', 'Paraná · PR', 'São Paulo · SP', 'Sergipe · SE', 'Tocantins · TO']);
});

test('simula o item e mostra alíquotas, redução, memória de cálculo e fundamentação', function (): void {
    fakeCalculadora(headers: ['x-warning-dados-simulados' => '6', 'X-CALC-DB-VERSION' => 'V0073']);

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario()))
        ->assertSuccessful()
        ->assertSee('CALCULO_SIMULADO')
        ->assertSee('Fornecimento dos serviços de educação (Anexo II)')
        ->assertSee('Alíquota reduzida em 60%')
        ->assertSee('R$ 1.200,00')
        ->assertSeeInOrder(['CBS', '8,80%', '60,00%', '3,52%', 'R$ 42,24'])
        ->assertSee('R$ 42,72')
        ->assertSee('Art. 129')
        ->assertSee('Base de dados da calculadora: V0073');
});

test('envia à calculadora os dados normalizados e as alíquotas a partir de 2027', function (): void {
    fakeCalculadora();

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario()))->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/calculadora/observabilidade/regime-geral')) {
            return false;
        }

        $item = $request['itens'][0];

        return $request['dhFatoGerador'] === '2027-03-10T00:00:00-03:00'
            && $request['municipio'] === 4314902
            && $request['uf'] === 'RS'
            && $item['nbs'] === '122011200'
            && $item['baseCalculo'] === '1200.00'
            && $item['aliquotasNominais'] === ['cbs' => '8.8', 'ibsEstadual' => '0.05', 'ibsMunicipal' => '0.05']
            && ! array_key_exists('ncm', $item);
    });
});

test('não envia alíquotas nominais antes de 2027', function (): void {
    fakeCalculadora(fixtureJson('calculadora/regular/observabilidade-resposta-2026-servico-educacao.json'));

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario(['data' => '2026-06-10'])))
        ->assertSuccessful()
        ->assertSee('alíquotas de referência da Receita para 2026')
        ->assertDontSee('CALCULO_SIMULADO');

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/regime-geral')
        && ! array_key_exists('aliquotasNominais', $request['itens'][0]));
});

test('valida o formulário', function (array $dados, string $campo): void {
    $this->from(route('simulacoes.item.create'))
        ->get(route('simulacoes.item.resultado', dadosDoFormulario($dados)))
        ->assertRedirect(route('simulacoes.item.create'))
        ->assertSessionHasErrors($campo);

    Http::assertNothingSent();
})->with([
    'sem NCM nem NBS'          => [['nbs' => ''], 'ncm'],
    'NCM e NBS juntos'         => [['ncm' => '10063021'], 'ncm'],
    'IS ad rem sem unidade'    => [['is_aliquota_ad_rem' => '1,25', 'is_quantidade' => '1'], 'is_unidade'],
    'IS com alíquota inválida' => [['is_aliquota_ad_valorem' => '-0'], 'is_aliquota_ad_valorem'],
    'NCM curto'                => [['nbs' => '', 'ncm' => '1006'], 'ncm'],
    'CST com 2 dígitos'        => [['cst' => '20'], 'cst'],
    'cClassTrib vazio'         => [['c_class_trib' => ''], 'c_class_trib'],
    'valor zero'               => [['valor' => '0'], 'valor'],
    'valor com 3 casas'        => [['valor' => '10,555'], 'valor'],
    'valor com letras'         => [['valor' => 'abc'], 'valor'],
    'UF inválida'              => [['uf' => 'XX'], 'uf'],
    'município de outra UF'    => [['municipio' => '3550308'], 'municipio'],
    'data antes da transição'  => [['data' => '2025-12-31'], 'data'],
    'data depois da transição' => [['data' => '2034-01-01'], 'data'],
    'alíquota ausente em 2027' => [['aliquota_cbs' => ''], 'aliquota_cbs'],
    'alíquota acima de 100%'   => [['aliquota_ibs_estadual' => '150'], 'aliquota_ibs_estadual'],
]);

test('mensagens de validação em português', function (): void {
    $this->from(route('simulacoes.item.create'))
        ->get(route('simulacoes.item.resultado', dadosDoFormulario(['cst' => '20', 'nbs' => ''])))
        ->assertSessionHasErrors([
            'cst' => 'CST deve ter 3 dígitos.',
            'ncm' => 'Informe o NCM (mercadoria) ou o NBS (serviço).',
        ]);
});

test('aceita alíquotas vazias antes de 2027', function (): void {
    fakeCalculadora(fixtureJson('calculadora/regular/observabilidade-resposta-2026-servico-educacao.json'));

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario([
        'data' => '2026-06-10', 'aliquota_cbs' => '', 'aliquota_ibs_estadual' => '', 'aliquota_ibs_municipal' => '',
    ])))->assertSuccessful();
});

test('mostra no campo certo o erro do item apontado pela calculadora', function (): void {
    fakeCalculadora(fixtureJson('calculadora/regular/observabilidade-resposta-todos-com-erro.json'));

    $this->from(route('simulacoes.item.create'))
        ->get(route('simulacoes.item.resultado', dadosDoFormulario(['nbs' => '', 'ncm' => '02013000', 'c_class_trib' => '200035'])))
        ->assertRedirect(route('simulacoes.item.create'))
        ->assertSessionHasErrors(['ncm' => 'NCM de código 02013000 não vinculada à Classificação Tributária de código 200035 (CBS e IBS)']);
});

test('mostra o erro quando a calculadora rejeita a operação', function (): void {
    fakeCalculadora(fixtureJson('calculadora/regular/observabilidade-422-sem-itens.json'), 422);

    $this->from(route('simulacoes.item.create'))
        ->get(route('simulacoes.item.resultado', dadosDoFormulario()))
        ->assertRedirect(route('simulacoes.item.create'))
        ->assertSessionHasErrors('simulacao');
});

test('avisa quando a calculadora está indisponível', function (): void {
    Http::fake(['*' => Http::failedConnection()]);

    $this->from(route('simulacoes.item.create'))
        ->get(route('simulacoes.item.resultado', dadosDoFormulario()))
        ->assertRedirect(route('simulacoes.item.create'))
        ->assertSessionHasErrors(['simulacao' => 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.'])
        ->assertSessionHasInput('c_class_trib', '200028');
});

test('mostra o resultado mesmo sem classificação e fundamentação nos dados abertos', function (): void {
    Http::fake([
        '*/calculadora/observabilidade/regime-geral' => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta-2027-servico-educacao.json')),
        '*/class-trib/*'                             => Http::response(fixtureJson('calculadora/regular/classificacao-tributaria-404.json'), 404),
        '*/fundamentacoes-legais*'                   => Http::response([]),
    ]);

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario()))
        ->assertSuccessful()
        ->assertSee('R$ 42,24')
        ->assertDontSee('Fundamentação legal');
});

test('lista os municípios da UF em JSON', function (): void {
    Http::fake(['*/dados-abertos/ufs/municipios*' => Http::response(fixtureJson('calculadora/regular/municipios-rs.json'))]);

    $this->getJson(route('dados-abertos.municipios', ['uf' => 'RS']))
        ->assertSuccessful()
        ->assertJsonCount(3)
        ->assertJsonFragment(['codigo' => 4314902, 'nome' => 'Porto Alegre']);
});

test('responde 404 para UF inexistente e 503 quando a calculadora está fora', function (): void {
    $this->getJson('/dados-abertos/ufs/XX/municipios')->assertNotFound();

    Http::fake(['*' => Http::failedConnection()]);

    $this->getJson(route('dados-abertos.municipios', ['uf' => 'RS']))
        ->assertStatus(503)
        ->assertJsonPath('message', 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.');
});

test('envia o grupo do IS para cerveja em 2027 e mostra o valor calculado', function (): void {
    $cerveja = fixtureJson('calculadora/regular/observabilidade-resposta.json')['objetos'][1];
    fakeCalculadora(['objetos' => [['nObj' => 1] + $cerveja]]);
    Http::fake(['*/dados-abertos/ncm?ncm=22030000&data=2027-03-10' => Http::response(fixtureJson('calculadora/regular/ncm-22030000.json'))]);

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario([
        'nbs'   => '', 'ncm' => '2203.00.00', 'cst' => '000', 'c_class_trib' => '000001',
        'valor' => '150,00', 'is_aliquota_ad_valorem' => '10,00', 'is_quantidade' => '12', 'is_unidade' => 'lt',
    ])))->assertSuccessful()
        ->assertSee('CALCULO_SIMULADO')
        ->assertSee('R$ 165,00')
        ->assertSeeInOrder(['Imposto Seletivo', '10,00%', 'R$ 15,00'])
        ->assertSee('R$ 29,68');

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/regime-geral')) {
            return false;
        }

        $item = $request['itens'][0];

        return ! isset($item['baseCalculo'])
            && $item['impostoSeletivo'] === ['cst' => '000', 'cClassTrib' => '000001', 'baseCalculo' => '150.00', 'quantidade' => '12', 'unidade' => 'LT']
            && $item['aliquotasNominais']['impostoSeletivo'] === ['adValorem' => '10.00'];
    });
});

test('pede a alíquota do IS quando a calculadora acusa REG-013', function (): void {
    $erro = fixtureJson('calculadora/regular/observabilidade-resposta-todos-com-erro.json');
    $erro['objetos'][0]['erros'][0]['code'] = 'REG-013';
    $erro['objetos'][0]['erros'][0]['pointer'] = '#/itens/0/impostoSeletivo';
    fakeCalculadora($erro);

    $this->from(route('simulacoes.item.create'))->get(route('simulacoes.item.resultado', dadosDoFormulario([
        'nbs' => '', 'ncm' => '22030000', 'cst' => '000', 'c_class_trib' => '000001',
    ])))->assertRedirect(route('simulacoes.item.create'))
        ->assertSessionHasErrors(['is_aliquota_ad_valorem' => ImpostoSeletivoDoItem::MENSAGEM_SEM_DADOS]);
});

test('mantém a base da operação e omite o IS quando o NCM não é tributado', function (): void {
    fakeCalculadora();
    Http::fake(['*/dados-abertos/ncm?ncm=10063021*' => Http::response(fixtureJson('calculadora/regular/ncm-10063021.json'))]);

    $this->get(route('simulacoes.item.resultado', dadosDoFormulario([
        'nbs' => '', 'ncm' => '10063021', 'cst' => '200', 'c_class_trib' => '200003', 'is_aliquota_ad_valorem' => '10',
    ])))->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/regime-geral')
        && $request['itens'][0]['baseCalculo'] === '1200.00'
        && ! isset($request['itens'][0]['impostoSeletivo'])
        && ! isset($request['itens'][0]['aliquotasNominais']['impostoSeletivo']));
});
