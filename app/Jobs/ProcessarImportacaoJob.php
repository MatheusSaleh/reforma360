<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use App\Models\Importacao;
use App\Services\Importacao\ImportarCatalogoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Processa o arquivo de uma importação. Se a calculadora (dados abertos) estiver fora,
 * tenta de novo mais tarde; o processamento é idempotente (produtos por descrição).
 */
class ProcessarImportacaoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 4;

    public int $timeout = 600;

    public function __construct(
        public Importacao $importacao,
    ) {
        $this->onQueue(config()->string('importacao.fila'));
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ImportarCatalogoService $service): void
    {
        try {
            $service->processar($this->importacao);
        } catch (CalculadoraIndisponivelException $e) {
            $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
        }
    }

    public function failed(?Throwable $e): void
    {
        app(ImportarCatalogoService::class)->falhar(
            $this->importacao,
            'Não foi possível processar a planilha agora. Tente importar novamente.',
        );
    }
}
