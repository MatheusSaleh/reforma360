<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use App\Enums\TipoDocumentoFiscal;
use App\Integrations\Calculadora\Contracts\XmlTributacao;
use App\Integrations\Calculadora\Data\Operacao;
use App\Integrations\Calculadora\Data\ResultadoValidacaoXml;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\PayloadInvalidoException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Geração (POST /regime-geral + POST /xml/generate) e validação (POST /xml/validate)
 * do XML dos grupos de tributação na calculadora do regime regular.
 */
final readonly class XmlTributacaoClient implements XmlTributacao
{
    public function __construct(
        private CalculadoraRegularHttp $http,
    ) {}

    public function gerarGrupo(Operacao $operacao, TipoDocumentoFiscal $tipo): string
    {
        // O /xml/generate recebe a resposta do cálculo "normal" (não a da observabilidade).
        $calculo = $this->enviar($operacao->id, fn (PendingRequest $http): Response => $http->post('calculadora/regime-geral', $operacao->toPayload()));

        $grupo = $this->enviar($operacao->id, fn (PendingRequest $http): Response => $http
            ->withBody($calculo->body(), 'application/json')
            ->accept('application/xml')
            ->post('calculadora/xml/generate?tipo='.$tipo->value));

        return $grupo->body();
    }

    public function validar(string $xml, TipoDocumentoFiscal $tipo, string $subtipo = 'grupo'): ResultadoValidacaoXml
    {
        try {
            $response = $this->http->request()
                ->withBody($xml, 'application/xml')
                ->post("calculadora/xml/validate?tipo={$tipo->value}&subtipo={$subtipo}");
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::semConexao(null, $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::erroDoServidor(null, $response->status());
        }

        if ($response->successful() && mb_trim($response->body()) === 'true') {
            return new ResultadoValidacaoXml(true);
        }

        $detalhe = $response->json('detail');

        return new ResultadoValidacaoXml(false, is_string($detalhe) ? $detalhe : 'XML rejeitado pelo XSD.');
    }

    /**
     * @param  callable(PendingRequest): Response  $requisicao
     */
    private function enviar(string $operacaoId, callable $requisicao): Response
    {
        try {
            $response = $requisicao($this->http->request());
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::semConexao($operacaoId, $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::erroDoServidor($operacaoId, $response->status());
        }

        if ($response->clientError()) {
            throw PayloadInvalidoException::fromResponse($operacaoId, $response);
        }

        return $response;
    }
}
