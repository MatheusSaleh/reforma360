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
        public readonly ?string $operacaoId = null,
        public readonly ?int $status = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function danfseIndisponivel(?int $status = null, ?Throwable $previous = null): self
    {
        return new self('O gerador de DANFSe não respondeu.', null, $status, $previous);
    }

    /**
     * Mensagem mostrada ao usuário.
     */
    public function mensagemParaUsuario(): string
    {
        return $this->getMessage() === 'O gerador de DANFSe não respondeu.'
            ? 'O gerador de DANFSe está indisponível no momento. Tente novamente em instantes.'
            : 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.';
    }

    public static function semConexao(?string $operacaoId, Throwable $previous): self
    {
        return new self('A calculadora da Receita não respondeu.', $operacaoId, previous: $previous);
    }

    public static function erroDoServidor(?string $operacaoId, int $status): self
    {
        return new self("A calculadora da Receita respondeu com erro {$status}.", $operacaoId, $status);
    }

    /**
     * @return array{operacao_id: string|null, status: int|null}
     */
    public function context(): array
    {
        return ['operacao_id' => $this->operacaoId, 'status' => $this->status];
    }
}
