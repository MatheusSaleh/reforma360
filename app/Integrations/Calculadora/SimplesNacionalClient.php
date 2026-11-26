<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use App\Integrations\Calculadora\Contracts\CalculadoraSimples;
use App\Integrations\Calculadora\Data\AtividadeSimples;
use App\Integrations\Calculadora\Data\OperacaoSimples;
use App\Integrations\Calculadora\Data\ResultadoItemSimples;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use App\Integrations\Calculadora\Exceptions\RespostaInesperadaException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Client da calculadora do Simples Nacional da Receita.
 */
final readonly class SimplesNacionalClient implements CalculadoraSimples
{
    public function __construct(
        private CalculadoraSimplesHttp $http,
    ) {}

    public function calcular(OperacaoSimples $operacao): array
    {
        $response = $this->enviar($operacao->id, fn (PendingRequest $http): Response => $http
            ->post('calculadora/observabilidade/simples-nacional', $operacao->toPayload()));

        if ($response->clientError()) {
            throw PayloadInvalidoException::fromResponse($operacao->id, $response);
        }

        $objetos = $response->json('objetos');

        if (! is_array($objetos)) {
            throw RespostaInesperadaException::campoAusente('objetos');
        }

        return array_values(array_map(
            fn (mixed $objeto): ResultadoItemSimples => is_array($objeto) ? ResultadoItemSimples::fromArray($objeto) : throw RespostaInesperadaException::campoInvalido('objetos'),
            $objetos,
        ));
    }

    public function atividades(CarbonImmutable $data): array
    {
        $lista = Cache::remember("calculadora:simples:atividades:{$data->toDateString()}", 86400, function () use ($data): array {
            $corpo = $this->enviar(null, fn (PendingRequest $http): Response => $http
                ->get('calculadora/simples-nacional/atividades', ['data' => $data->toDateString()]))->throw()->json();

            return is_array($corpo) ? array_values(array_filter($corpo, is_array(...))) : throw RespostaInesperadaException::corpoInvalido();
        });

        return array_map(AtividadeSimples::fromArray(...), $lista);
    }

    /**
     * @param  callable(PendingRequest): Response  $requisicao
     */
    private function enviar(?string $operacaoId, callable $requisicao): Response
    {
        try {
            $response = $requisicao($this->http->request());
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::semConexao($operacaoId, $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::erroDoServidor($operacaoId, $response->status());
        }

        return $response;
    }
}
