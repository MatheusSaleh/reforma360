<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Events\SimulacaoAtualizada;
use App\Models\Simulacao;
use Throwable;

/**
 * Transmite o progresso sem derrubar o job: se o Reverb estiver fora, a simulação
 * continua e a tela se atualiza pelo fallback (recarregamento periódico).
 */
final class TransmitirProgresso
{
    public function __invoke(Simulacao $simulacao): void
    {
        try {
            SimulacaoAtualizada::dispatch($simulacao->fresh() ?? $simulacao);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
