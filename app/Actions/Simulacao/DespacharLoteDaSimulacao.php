<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Jobs\SimularItensJob;
use App\Models\Simulacao;
use Illuminate\Bus\Batch;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Bus;

/**
 * Despacha o lote de jobs de uma simulação (um job por bloco de itens = uma chamada
 * à calculadora); ao terminar o lote, FinalizarSimulacao fecha a simulação.
 */
final readonly class DespacharLoteDaSimulacao
{
    public function __construct(
        #[Config('simulacao.itens_por_chamada')]
        private int $itensPorChamada = 50,
    ) {}

    public function handle(Simulacao $simulacao): void
    {
        $jobs = [];

        $simulacao->itens()->orderBy('id')->select('id')->chunkById(max(1, $this->itensPorChamada), function ($itens) use ($simulacao, &$jobs): void {
            $jobs[] = new SimularItensJob($simulacao->id, array_values($itens->pluck('id')->map(fn (mixed $id): int => (int) $id)->all()));
        });

        $simulacaoId = $simulacao->id;

        $batch = Bus::batch($jobs)
            ->name("Simulação #{$simulacaoId}")
            ->onQueue('simulacoes')
            ->allowFailures()
            ->finally(function (Batch $batch) use ($simulacaoId): void {
                app(FinalizarSimulacao::class)->handle($simulacaoId);
            })
            ->dispatch();

        $simulacao->update(['batch_id' => $batch->id]);
    }
}
