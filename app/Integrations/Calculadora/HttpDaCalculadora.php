<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Requisição HTTP pré-configurada para uma calculadora da Receita: base URL,
 * timeouts e novas tentativas só em falha de conexão ou 5xx.
 */
abstract readonly class HttpDaCalculadora
{
    public function __construct(
        private string $baseUrl,
        private int $timeout,
        private int $connectTimeout,
        private int $tentativas,
        private int $intervaloEntreTentativasMs,
    ) {}

    public function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->connectTimeout($this->connectTimeout)
            ->timeout($this->timeout)
            ->retry(
                $this->tentativas,
                fn (int $tentativa): int => $this->intervaloEntreTentativasMs * $tentativa,
                fn (Throwable $e): bool => $e instanceof ConnectionException
                    || ($e instanceof RequestException && $e->response->serverError()),
                throw: false,
            );
    }
}
