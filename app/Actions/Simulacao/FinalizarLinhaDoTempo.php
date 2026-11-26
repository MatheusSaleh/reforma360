<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Enums\StatusSimulacao;
use App\Models\LinhaDoTempo;
use App\Notifications\LinhaDoTempoConcluida;

/**
 * Fecha a linha do tempo quando o último ano termina e avisa o usuário uma única vez
 * (a atualização condicional evita aviso duplicado se dois anos terminarem juntos).
 */
final class FinalizarLinhaDoTempo
{
    public function handle(int $linhaDoTempoId): void
    {
        $linha = LinhaDoTempo::query()->find($linhaDoTempoId);

        if ($linha === null) {
            return;
        }

        $statusDosAnos = $linha->simulacoesDosAnos()->pluck('status')->all();

        foreach ($statusDosAnos as $status) {
            if ($status->emAndamento()) {
                return;
            }
        }

        $status = match (true) {
            array_all($statusDosAnos, fn (StatusSimulacao $s): bool => $s === StatusSimulacao::Falhou)    => StatusSimulacao::Falhou,
            array_all($statusDosAnos, fn (StatusSimulacao $s): bool => $s === StatusSimulacao::Concluida) => StatusSimulacao::Concluida,
            default                                                                                       => StatusSimulacao::ConcluidaComErros,
        };

        $atualizou = LinhaDoTempo::query()
            ->whereKey($linha->id)
            ->where('status', StatusSimulacao::Processando)
            ->update(['status' => $status, 'concluida_em' => now()]);

        if ($atualizou === 1) {
            $linha->refresh();
            $linha->usuario?->notify(new LinhaDoTempoConcluida($linha));
        }
    }
}
