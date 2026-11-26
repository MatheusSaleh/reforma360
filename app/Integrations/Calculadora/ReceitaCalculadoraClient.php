<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Data\ResultadoOperacao;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;

/**
 * Client HTTP da calculadora do regime regular da Receita Federal.
 *
 * Usa o endpoint de observabilidade, que devolve o estado e os erros de cada item
 * em vez de rejeitar a operação inteira no primeiro erro.
 */
final readonly class ReceitaCalculadoraClient implements CalculadoraTributos
{
    private const string ENDPOINT = 'calculadora/observabilidade/regime-geral';

    public function __construct(
        private CalculadoraRegularHttp $http,
    ) {}

    public function calcular(Operacao $operacao): ResultadoOperacao
    {
        try {
            $response = $this->http->request()->post(self::ENDPOINT, $operacao->toPayload());
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::semConexao($operacao->id, $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::erroDoServidor($operacao->id, $response->status());
        }

        if ($response->clientError()) {
            throw PayloadInvalidoException::fromResponse($operacao->id, $response);
        }

        return $this->resultado($response);
    }

    private function resultado(Response $response): ResultadoOperacao
    {
        $corpo = $response->json();

        if (! is_array($corpo)) {
            throw RespostaInesperadaException::corpoInvalido();
        }

        return ResultadoOperacao::fromArray(
            $corpo,
            avisoDadosSimulados: $this->header($response, 'x-warning-dados-simulados'),
            versaoApp: $this->header($response, 'X-CALC-APP-VERSION'),
            versaoBaseDados: $this->header($response, 'X-CALC-DB-VERSION'),
        );
    }

    private function header(Response $response, string $nome): ?string
    {
        $valor = $response->header($nome);

        return $valor === '' ? null : $valor;
    }
}
