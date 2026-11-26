<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Importacao;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Apaga os XMLs de NFS-e guardados para o DANFSe depois da retenção
 * (config importacao.retencao_xml_dias). Agendado diariamente.
 */
#[Signature('importacoes:apagar-arquivos-vencidos')]
#[Description('Apaga os arquivos de importação guardados além do prazo de retenção')]
class ApagarArquivosVencidosCommand extends Command
{
    public function handle(): int
    {
        $limite = now()->subDays(config()->integer('importacao.retencao_xml_dias'));
        $apagados = 0;

        Importacao::query()
            ->whereNotNull('caminho_arquivo')
            ->whereNotNull('concluida_em')
            ->where('concluida_em', '<', $limite)
            ->lazyById()
            ->each(function (Importacao $importacao) use (&$apagados): void {
                Storage::disk('local')->delete((string) $importacao->caminho_arquivo);
                $importacao->update(['caminho_arquivo' => null]);
                $apagados++;
            });

        $this->info("{$apagados} arquivo(s) apagado(s).");

        return self::SUCCESS;
    }
}
