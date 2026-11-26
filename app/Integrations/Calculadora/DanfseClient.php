<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use App\Integrations\Calculadora\Contracts\GeradorDanfse;
use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Integrations\Calculadora\Exceptions\DanfseRejeitadoException;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Gerador de DANFSe da Receita (serviço Go): POST /danfse com o XML da NFS-e → PDF.
 */
final readonly class DanfseClient implements GeradorDanfse
{
    public function __construct(
        #[Config('services.danfse.url')]
        private string $baseUrl,
        #[Config('services.danfse.timeout')]
        private int $timeout = 20,
    ) {}

    public function gerar(string $xmlDaNfse): string
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->accept('application/pdf')
                ->connectTimeout(3)
                ->timeout($this->timeout)
                ->retry(2, 200, fn (Throwable $e): bool => $e instanceof ConnectionException
                    || ($e instanceof RequestException && $e->response->serverError()), throw: false)
                ->withBody($xmlDaNfse, 'application/xml')
                ->post('danfse');
        } catch (ConnectionException $e) {
            throw CalculadoraIndisponivelException::danfseIndisponivel(previous: $e);
        }

        if ($response->serverError()) {
            throw CalculadoraIndisponivelException::danfseIndisponivel($response->status());
        }

        if ($response->clientError() || ! str_starts_with($response->body(), '%PDF')) {
            $mensagem = $response->json('mensagem');

            throw new DanfseRejeitadoException('O gerador de DANFSe recusou a nota'.(is_string($mensagem) ? ": {$mensagem}" : '.'));
        }

        return $response->body();
    }
}
