<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Simulacao;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Progresso de uma simulação em lote, transmitido pelo Reverb no canal privado da simulação.
 * Só contadores: nenhum dado fiscal sai no WebSocket.
 */
class SimulacaoAtualizada implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public readonly Simulacao $simulacao,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("simulacoes.{$this->simulacao->id}");
    }

    public function broadcastAs(): string
    {
        return 'simulacao.atualizada';
    }

    /**
     * @return array{status: string, status_label: string, total: int, processados: int, com_erro: int, percentual: int, em_andamento: bool}
     */
    public function broadcastWith(): array
    {
        return [
            'status'       => $this->simulacao->status->value,
            'status_label' => $this->simulacao->status->label(),
            'total'        => $this->simulacao->total_itens,
            'processados'  => $this->simulacao->itens_processados,
            'com_erro'     => $this->simulacao->itens_com_erro,
            'percentual'   => $this->simulacao->percentualConcluido(),
            'em_andamento' => $this->simulacao->status->emAndamento(),
        ];
    }
}
