<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Enums\EstadoItemCalculo;
use App\Enums\StatusSimulacao;
use App\Models\Simulacao;
use App\Notifications\SimulacaoConcluida;
use App\Services\Simulacao\TransmitirProgresso;

/**
 * Fecha a simulação quando o lote termina: marca itens que ficaram sem resultado,
 * recalcula contadores e totais, define o status e avisa o usuário.
 */
final readonly class FinalizarSimulacao
{
    public function __construct(
        private TransmitirProgresso $transmitir,
    ) {}

    public function handle(int $simulacaoId): void
    {
        $simulacao = Simulacao::query()->find($simulacaoId);

        if ($simulacao === null || ! $simulacao->status->emAndamento()) {
            return;
        }

        $simulacao->itens()->whereNull('estado')->update([
            'estado' => EstadoItemCalculo::FalhaTecnica->value,
            'erros'  => json_encode([['codigo' => null, 'mensagem' => 'Não foi possível simular este item agora. Inicie uma nova simulação.']]),
        ]);

        $totais = ['base' => '0', 'cbs' => '0', 'ibs' => '0', 'is' => '0', 'tributos' => '0'];
        $calculados = 0;

        foreach ($simulacao->itens()->whereIn('estado', [EstadoItemCalculo::Calculado, EstadoItemCalculo::CalculoSimulado])->lazyById(500) as $item) {
            $calculados++;
            $totais['base'] = bcadd($totais['base'], $item->base_calculo, 2);
            $totais['cbs'] = bcadd($totais['cbs'], $item->valor_cbs ?? '0', 2);
            $totais['ibs'] = bcadd($totais['ibs'], $item->valor_ibs ?? '0', 2);
            $totais['is'] = bcadd($totais['is'], $item->valor_imposto_seletivo ?? '0', 2);
            $totais['tributos'] = bcadd($totais['tributos'], $item->total_tributos ?? '0', 2);
        }

        $comErro = $simulacao->total_itens - $calculados;

        $simulacao->update([
            'status' => match (true) {
                $calculados === 0 => StatusSimulacao::Falhou,
                $comErro > 0      => StatusSimulacao::ConcluidaComErros,
                default           => StatusSimulacao::Concluida,
            },
            'itens_processados'      => $simulacao->total_itens,
            'itens_com_erro'         => $comErro,
            'total_base_calculo'     => $calculados === 0 ? null : $totais['base'],
            'total_cbs'              => $calculados === 0 ? null : $totais['cbs'],
            'total_ibs'              => $calculados === 0 ? null : $totais['ibs'],
            'total_imposto_seletivo' => $calculados === 0 ? null : $totais['is'],
            'total_tributos'         => $calculados === 0 ? null : $totais['tributos'],
            'concluida_em'           => now(),
        ]);

        ($this->transmitir)($simulacao);
        $simulacao->usuario?->notify(new SimulacaoConcluida($simulacao));
    }
}
