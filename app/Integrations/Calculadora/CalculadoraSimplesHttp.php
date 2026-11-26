<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora;

use Illuminate\Container\Attributes\Config;

/**
 * HTTP da calculadora do Simples Nacional (mesmos timeouts e retries da do regime regular).
 */
final readonly class CalculadoraSimplesHttp extends HttpDaCalculadora
{
    public function __construct(
        #[Config('services.calculadora_simples.url')]
        string $baseUrl,
        #[Config('services.calculadora_regular.timeout')]
        int $timeout = 15,
        #[Config('services.calculadora_regular.connect_timeout')]
        int $connectTimeout = 3,
        #[Config('services.calculadora_regular.retries')]
        int $tentativas = 3,
        #[Config('services.calculadora_regular.retry_sleep_ms')]
        int $intervaloEntreTentativasMs = 200,
    ) {
        parent::__construct($baseUrl, $timeout, $connectTimeout, $tentativas, $intervaloEntreTentativasMs);
    }
}
