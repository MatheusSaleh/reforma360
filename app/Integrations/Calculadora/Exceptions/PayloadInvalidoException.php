<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Exceptions;

use App\Integrations\Calculadora\Data\ErroCalculo;
use Illuminate\Http\Client\Response;
use RuntimeException;

/**
 * A calculadora rejeitou a operação inteira (HTTP 4xx). Não adianta tentar de novo
 * com o mesmo payload.
 */
final class PayloadInvalidoException extends RuntimeException
{
    /**
     * @param  list<ErroCalculo>  $erros
     */
    public function __construct(
        string $message,
        public readonly string $operacaoId,
        public readonly int $status,
        public readonly array $erros = [],
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(string $operacaoId, Response $response): self
    {
        $corpo = $response->json();
        $corpo = is_array($corpo) ? $corpo : [];

        $erros = [];

        foreach ($corpo['errors'] ?? [] as $erro) {
            if (is_array($erro)) {
                $erros[] = ErroCalculo::fromArray($erro);
            }
        }

        $detalhe = is_string($corpo['detail'] ?? null) ? $corpo['detail'] : 'Operação rejeitada pela calculadora.';

        return new self($detalhe, $operacaoId, $response->status(), $erros);
    }

    /**
     * @return array{operacao_id: string, status: int, codigos: list<string>}
     */
    public function context(): array
    {
        return [
            'operacao_id' => $this->operacaoId,
            'status'      => $this->status,
            'codigos'     => array_map(fn (ErroCalculo $erro): string => $erro->codigo, $this->erros),
        ];
    }
}
