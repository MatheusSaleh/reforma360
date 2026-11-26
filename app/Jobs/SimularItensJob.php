<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Models\Simulacao;
use App\Services\Simulacao\SimularLoteService;
use App\Services\Simulacao\TransmitirProgresso;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Simula um bloco de itens (uma chamada à calculadora) dentro do lote da simulação.
 */
class SimularItensJob implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 4;

    public int $timeout = 90;

    /**
     * @param  list<int>  $idsDosItens
     */
    public function __construct(
        public int $simulacaoId,
        public array $idsDosItens,
    ) {
        $this->onQueue('simulacoes');
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(SimularLoteService $service, TransmitirProgresso $transmitir): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $simulacao = Simulacao::query()->find($this->simulacaoId);

        if ($simulacao === null) {
            return;
        }

        try {
            $service->processar($simulacao, $this->idsDosItens);
        } catch (CalculadoraIndisponivelException) {
            $this->release($this->backoff()[$this->attempts() - 1] ?? 180);

            return;
        }

        $transmitir($simulacao);
    }
}
