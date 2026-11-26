<?php

declare(strict_types=1);

use App\Enums\EstadoItemCalculo;
use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Integrations\Calculadora\Data\ItemOperacao;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use App\Integrations\Calculadora\ReceitaCalculadoraClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

const URL_OBSERVABILIDADE = 'http://calculadora-regular:8080/api/calculadora/observabilidade/regime-geral';

beforeEach(function (): void {
    Http::preventStrayRequests();
    Sleep::fake();
    config()->set('services.calculadora_regular.url', 'http://calculadora-regular:8080/api');
});

function operacaoDeExemplo(): Operacao
{
    $aliquotas = new AliquotasNominais(cbs: '8.8', ibsEstadual: '0.05', ibsMunicipal: '0.05');

    return new Operacao(
        id: 'op-teste-1',
        dhFatoGerador: CarbonImmutable::parse('2027-03-10T10:00:00-03:00'),
        municipio: 4314902,
        uf: 'RS',
        itens: [
            new ItemOperacao(numero: 1, cst: '200', cClassTrib: '200003', baseCalculo: '100.00', ncm: '10063021', quantidade: '20', unidade: 'KG', aliquotasNominais: $aliquotas),
            new ItemOperacao(numero: 3, cst: '200', cClassTrib: '200028', baseCalculo: '1200.00', nbs: '122011200', aliquotasNominais: $aliquotas),
        ],
    );
}

function calculadora(): CalculadoraTributos
{
    return app(CalculadoraTributos::class);
}

test('o contrato resolve para o client da Receita', function (): void {
    expect(calculadora())->toBeInstanceOf(ReceitaCalculadoraClient::class);
});

test('envia a operação para o endpoint de observabilidade com decimais em string', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta.json'))]);

    calculadora()->calcular(operacaoDeExemplo());

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && $request->url() === URL_OBSERVABILIDADE
            && $request->hasHeader('Accept', 'application/json')
            && $request['id'] === 'op-teste-1'
            && $request['dhFatoGerador'] === '2027-03-10T10:00:00-03:00'
            && $request['municipio'] === 4314902
            && $request['itens'][0] === [
                'numero'            => 1,
                'ncm'               => '10063021',
                'cst'               => '200',
                'cClassTrib'        => '200003',
                'baseCalculo'       => '100.00',
                'quantidade'        => '20',
                'unidade'           => 'KG',
                'aliquotasNominais' => ['cbs' => '8.8', 'ibsEstadual' => '0.05', 'ibsMunicipal' => '0.05'],
            ]
            && $request['itens'][1]['nbs'] === '122011200'
            && ! array_key_exists('ncm', $request['itens'][1]);
    });
    Http::assertSentCount(1);
});

test('converte a resposta em DTOs com o estado e os tributos de cada item', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(
        fixtureJson('calculadora/regular/observabilidade-resposta.json'),
        headers: ['x-warning-dados-simulados' => '6', 'X-CALC-APP-VERSION' => '1.7.2', 'X-CALC-DB-VERSION' => 'V0073'],
    )]);

    $resultado = calculadora()->calcular(operacaoDeExemplo());

    expect($resultado->itens)->toHaveCount(4)
        ->and($resultado->ehSimulado())->toBeTrue()
        ->and($resultado->avisoDadosSimulados)->toBe('6')
        ->and($resultado->versaoApp)->toBe('1.7.2')
        ->and($resultado->versaoBaseDados)->toBe('V0073');

    $servicoEducacao = $resultado->item(3);
    expect($servicoEducacao->estado)->toBe(EstadoItemCalculo::CalculoSimulado)
        ->and($servicoEducacao->foiCalculado())->toBeTrue()
        ->and($servicoEducacao->ibsCbs->baseCalculo)->toBe('1200.00')
        ->and($servicoEducacao->ibsCbs->cbs->aliquota)->toBe('8.80')
        ->and($servicoEducacao->ibsCbs->cbs->percentualReducao)->toBe('60.00')
        ->and($servicoEducacao->ibsCbs->cbs->aliquotaEfetiva)->toBe('3.52')
        ->and($servicoEducacao->ibsCbs->cbs->valor)->toBe('42.24')
        ->and($servicoEducacao->ibsCbs->ibsEstadual->valor)->toBe('0.24')
        ->and($servicoEducacao->ibsCbs->ibsMunicipal->valor)->toBe('0.24')
        ->and($servicoEducacao->ibsCbs->valorIbs)->toBe('0.48')
        ->and($servicoEducacao->ibsCbs->memoriaCbs)->toContain('Art. 129');
});

test('lê o imposto seletivo e itens sem redução de alíquota', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta.json'))]);

    $cerveja = calculadora()->calcular(operacaoDeExemplo())->item(2);

    expect($cerveja->impostoSeletivo->aliquota)->toBe('10.00')
        ->and($cerveja->impostoSeletivo->baseCalculo)->toBe('150.00')
        ->and($cerveja->impostoSeletivo->valor)->toBe('15.00')
        ->and($cerveja->ibsCbs->baseCalculo)->toBe('165.00')
        ->and($cerveja->ibsCbs->cbs->percentualReducao)->toBeNull()
        ->and($cerveja->ibsCbs->cbs->valor)->toBe('14.52');
});

test('mantém o erro de um item sem interromper os demais', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta.json'))]);

    $resultado = calculadora()->calcular(operacaoDeExemplo());
    $itemComErro = $resultado->item(4);

    expect($itemComErro->estado)->toBe(EstadoItemCalculo::InconsistenciaEntrada)
        ->and($itemComErro->foiCalculado())->toBeFalse()
        ->and($itemComErro->ibsCbs)->toBeNull()
        ->and($itemComErro->erros)->toHaveCount(1)
        ->and($itemComErro->erros[0]->codigo)->toBe('REG-011')
        ->and($itemComErro->erros[0]->titulo)->toBe('NCM não vinculada')
        ->and($itemComErro->erros[0]->campo)->toBe('#/itens/3/ncm')
        ->and($resultado->itensComErro())->toHaveCount(1)
        ->and($resultado->item(1)->foiCalculado())->toBeTrue();
});

test('lê os totais, que somam só os itens calculados', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta.json'))]);

    $total = calculadora()->calcular(operacaoDeExemplo())->total;

    expect($total->baseCalculoIbsCbs)->toBe('1465.00')
        ->and($total->valorCbs)->toBe('56.76')
        ->and($total->valorIbs)->toBe('0.64')
        ->and($total->valorIbsEstadual)->toBe('0.32')
        ->and($total->valorIbsMunicipal)->toBe('0.32')
        ->and($total->valorImpostoSeletivo)->toBe('15.00');
});

test('aceita resposta sem totais quando nenhum item foi calculado', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-resposta-todos-com-erro.json'))]);

    $resultado = calculadora()->calcular(operacaoDeExemplo());

    expect($resultado->total)->toBeNull()
        ->and($resultado->itensComErro())->toHaveCount(1)
        ->and($resultado->ehSimulado())->toBeFalse();
});

test('lança PayloadInvalidoException com os erros quando a operação inteira é rejeitada', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(fixtureJson('calculadora/regular/observabilidade-422-sem-itens.json'), 422)]);

    try {
        calculadora()->calcular(operacaoDeExemplo());
        $this->fail('Deveria lançar PayloadInvalidoException.');
    } catch (PayloadInvalidoException $e) {
        expect($e->status)->toBe(422)
            ->and($e->operacaoId)->toBe('op-teste-1')
            ->and($e->getMessage())->toBe('O cálculo contém 1 erro(s).')
            ->and($e->erros)->toHaveCount(1)
            ->and($e->erros[0]->codigo)->toBe('VAL-003')
            ->and($e->context())->toBe(['operacao_id' => 'op-teste-1', 'status' => 422, 'codigos' => ['VAL-003']]);
    }

    Http::assertSentCount(1);
});

test('não tenta de novo em erro 4xx sem lista de erros', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(['status' => 400, 'detail' => 'JSON parse error'], 400)]);

    expect(fn () => calculadora()->calcular(operacaoDeExemplo()))
        ->toThrow(PayloadInvalidoException::class, 'JSON parse error');

    Http::assertSentCount(1);
});

test('tenta de novo em erro 5xx e devolve o resultado quando a calculadora volta', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::sequence()
        ->push(['status' => 503], 503)
        ->push(fixtureJson('calculadora/regular/observabilidade-resposta.json')),
    ]);

    $resultado = calculadora()->calcular(operacaoDeExemplo());

    expect($resultado->itens)->toHaveCount(4);
    Http::assertSentCount(2);
});

test('lança CalculadoraIndisponivelException depois de esgotar as tentativas em 5xx', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response(['status' => 500], 500)]);

    expect(fn () => calculadora()->calcular(operacaoDeExemplo()))
        ->toThrow(CalculadoraIndisponivelException::class, 'erro 500');

    Http::assertSentCount(3);
});

test('lança CalculadoraIndisponivelException quando não há conexão', function (): void {
    Http::fake([URL_OBSERVABILIDADE => Http::failedConnection()]);

    expect(fn () => calculadora()->calcular(operacaoDeExemplo()))
        ->toThrow(CalculadoraIndisponivelException::class, 'não respondeu');
});

test('lança RespostaInesperadaException quando o contrato da resposta muda', function (array $corpo): void {
    Http::fake([URL_OBSERVABILIDADE => Http::response($corpo)]);

    expect(fn () => calculadora()->calcular(operacaoDeExemplo()))
        ->toThrow(RespostaInesperadaException::class);
})->with([
    'estado desconhecido' => [['objetos' => [['nObj' => 1, 'estadoItem' => 'OUTRO']]]],
    'item sem número'     => [['objetos' => [['estadoItem' => 'CALCULADO']]]],
    'valor não numérico'  => [['objetos' => [['nObj' => 1, 'estadoItem' => 'CALCULADO', 'tribCalc' => ['IS' => [
        'CSTIS' => '000', 'cClassTribIS' => '000001', 'vBCIS' => 'abc', 'pIS' => '10.00', 'vIS' => '15.05',
    ]]]]]],
    'valor numérico com fração' => [['objetos' => [['nObj' => 1, 'estadoItem' => 'CALCULADO', 'tribCalc' => ['IS' => [
        'CSTIS' => '000', 'cClassTribIS' => '000001', 'vBCIS' => 150.5, 'pIS' => '10.00', 'vIS' => '15.05',
    ]]]]]],
]);
