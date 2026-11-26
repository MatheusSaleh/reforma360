<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Requisição HTTP pré-configurada para a calculadora do regime regular:
 * base URL, timeouts e novas tentativas só em falha de conexão ou 5xx.
 */
final readonly class CalculadoraRegularHttp
{
    public function __construct(
        #[Config('services.calculadora_regular.url')]
        private string $baseUrl,
        #[Config('services.calculadora_regular.timeout')]
        private int $timeout = 15,
        #[Config('services.calculadora_regular.connect_timeout')]
        private int $connectTimeout = 3,
        #[Config('services.calculadora_regular.retries')]
        private int $tentativas = 3,
        #[Config('services.calculadora_regular.retry_sleep_ms')]
        private int $intervaloEntreTentativasMs = 200,
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
