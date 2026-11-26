<?php

declare(strict_types=1);

namespace App\Services\Simulacao;

use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Services\Simulacao\Data\AnoDaLinhaDoTempo;

/**
 * Valores ano a ano da linha do tempo: do total da simulação ou de um item.
 */
final class LinhaDoTempoService
{
    /**
     * @return list<AnoDaLinhaDoTempo>
     */
    public function anos(LinhaDoTempo $linha, ?SimulacaoItem $itemDeOrigem = null): array
    {
        $simulacoes = $linha->simulacoesDosAnos()->get();
        $itensPorSimulacao = $itemDeOrigem === null ? collect() : SimulacaoItem::query()
            ->where('origem_id', $itemDeOrigem->id)
            ->whereIn('simulacao_id', $simulacoes->modelKeys())
            ->get()
            ->keyBy('simulacao_id');

        return array_values($simulacoes->map(function (Simulacao $simulacao) use ($itemDeOrigem, $itensPorSimulacao): AnoDaLinhaDoTempo {
            if ($itemDeOrigem === null) {
                return $this->doTotal($simulacao);
            }

            $item = $itensPorSimulacao->get($simulacao->id);

            return $this->doItem($simulacao, $item instanceof SimulacaoItem ? $item : null);
        })->all());
    }

    private function doTotal(Simulacao $simulacao): AnoDaLinhaDoTempo
    {
        $cbs = $simulacao->total_cbs;
        $ibs = $simulacao->total_ibs;
        $total = $cbs === null || $ibs === null ? null : bcadd($cbs, $ibs, 2);

        return new AnoDaLinhaDoTempo(
            ano: $simulacao->data_fato_gerador->year,
            simulacaoId: $simulacao->id,
            status: $simulacao->status,
            aliquotas: $simulacao->aliquotasNominais(),
            base: $simulacao->total_base_calculo,
            cbs: $cbs,
            ibs: $ibs,
            total: $total,
            carga: self::carga($total, $simulacao->total_base_calculo),
            itensComErro: $simulacao->itens_com_erro,
        );
    }

    private function doItem(Simulacao $simulacao, ?SimulacaoItem $item): AnoDaLinhaDoTempo
    {
        $calculado = $item !== null && $item->estado !== null && $item->estado->foiCalculado();
        $cbs = $calculado ? $item->valor_cbs : null;
        $ibs = $calculado ? $item->valor_ibs : null;
        $total = $cbs === null || $ibs === null ? null : bcadd($cbs, $ibs, 2);

        return new AnoDaLinhaDoTempo(
            ano: $simulacao->data_fato_gerador->year,
            simulacaoId: $simulacao->id,
            status: $simulacao->status,
            aliquotas: $simulacao->aliquotasNominais(),
            base: $item?->base_calculo,
            cbs: $cbs,
            ibs: $ibs,
            total: $total,
            carga: self::carga($total, $item?->base_calculo),
            erro: $item !== null && $item->temErro() ? (string) ($item->erros[0]['mensagem'] ?? $item->estado?->label()) : null,
        );
    }

    /**
     * @param  numeric-string|null  $total
     * @param  numeric-string|null  $base
     * @return numeric-string|null
     */
    private static function carga(?string $total, ?string $base): ?string
    {
        if ($total === null || $base === null || bccomp($base, '0', 2) <= 0) {
            return null;
        }

        return bcdiv(bcmul($total, '100', 4), $base, 2);
    }
}
