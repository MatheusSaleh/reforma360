<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Data;

/**
 * Resultado da validação de um XML contra o XSD oficial (POST /xml/validate).
 */
final readonly class ResultadoValidacaoXml
{
    public function __construct(
        public bool $valido,
        public ?string $mensagem = null,
    ) {}
}
