<?php

declare(strict_types=1);

namespace App\Integrations\Calculadora\Exceptions;

use RuntimeException;
use Throwable;

/**
 * A calculadora não respondeu (falha de conexão, timeout ou 5xx depois das novas tentativas).
 * Erro transitório: quem chama pode tentar de novo mais tarde.
 */
final class CalculadoraIndisponivelException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $operacaoId,
        public readonly ?int $status = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function semConexao(string $operacaoId, Throwable $previous): self
    {
        return new self('A calculadora da Receita não respondeu.', $operacaoId, previous: $previous);
    }

    public static function erroDoServidor(string $operacaoId, int $status): self
    {
        return new self("A calculadora da Receita respondeu com erro {$status}.", $operacaoId, $status);
    }

    /**
     * @return array{operacao_id: string, status: int|null}
     */
    public function context(): array
    {
        return ['operacao_id' => $this->operacaoId, 'status' => $this->status];
    }
}
