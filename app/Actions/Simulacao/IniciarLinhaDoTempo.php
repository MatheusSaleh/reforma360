<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Enums\StatusSimulacao;
use App\Integrations\Calculadora\Data\AliquotasNominais;
use App\Models\LinhaDoTempo;
use App\Models\Simulacao;
use App\Models\SimulacaoItem;
use App\Models\User;
use App\Services\Simulacao\CronogramaTransicaoService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cria a linha do tempo de uma simulação concluída: uma simulação por ano do
 * cronograma (2026→2033), com os mesmos itens e local, as alíquotas do ano
 * (cronograma sobre as plenas estimadas) e o mesmo dia/mês do fato gerador.
 * Refazer substitui a linha do tempo anterior.
 */
final readonly class IniciarLinhaDoTempo
{
    public function __construct(
        private CronogramaTransicaoService $cronograma,
        private DespacharLoteDaSimulacao $despacharLote,
    ) {}

    /**
     * @throws ValidationException quando a simulação de origem não pode gerar linha do tempo
     */
    public function handle(User $usuario, Simulacao $origem, AliquotasNominais $plenas): LinhaDoTempo
    {
        if ($origem->ehAnoDeLinhaDoTempo()) {
            throw ValidationException::withMessages(['linha_do_tempo' => 'Abra a simulação principal para gerar a linha do tempo.']);
        }

        if ($origem->status->emAndamento() || ! $origem->itens()->exists()) {
            throw ValidationException::withMessages(['linha_do_tempo' => 'Aguarde a simulação terminar para gerar a linha do tempo.']);
        }

        $simulacoes = DB::transaction(function () use ($usuario, $origem, $plenas): array {
            $origem->linhaDoTempo()->delete();

            $linha = $origem->empresa->linhasDoTempo()->create([
                'user_id'                      => $usuario->id,
                'simulacao_id'                 => $origem->id,
                'status'                       => StatusSimulacao::Processando,
                'aliquota_plena_cbs'           => $plenas->cbs,
                'aliquota_plena_ibs_estadual'  => $plenas->ibsEstadual,
                'aliquota_plena_ibs_municipal' => $plenas->ibsMunicipal,
            ]);

            $simulacoes = [];

            foreach ($this->cronograma->anos() as $ano) {
                $simulacoes[] = $this->simulacaoDoAno($linha, $origem, $ano, $plenas);
            }

            return [$linha, $simulacoes];
        });

        [$linha, $anos] = $simulacoes;

        foreach ($anos as $simulacao) {
            $this->despacharLote->handle($simulacao);
        }

        return $linha;
    }

    private function simulacaoDoAno(LinhaDoTempo $linha, Simulacao $origem, int $ano, AliquotasNominais $plenas): Simulacao
    {
        $aliquotas = $this->cronograma->aliquotasDoAno($ano, $plenas);
        $dataOrigem = $origem->data_fato_gerador;
        $data = CarbonImmutable::create($ano, $dataOrigem->month, 1)?->startOfDay() ?? CarbonImmutable::create($ano);
        $data = $data->day(min($dataOrigem->day, $data->daysInMonth));

        $simulacao = $origem->empresa->simulacoes()->create([
            'user_id'                => $linha->user_id,
            'importacao_id'          => $origem->importacao_id,
            'linha_do_tempo_id'      => $linha->id,
            'status'                 => StatusSimulacao::Pendente,
            'data_fato_gerador'      => $data->toDateString(),
            'uf'                     => $origem->uf,
            'municipio'              => $origem->municipio,
            'aliquota_cbs'           => $aliquotas?->cbs,
            'aliquota_ibs_estadual'  => $aliquotas?->ibsEstadual,
            'aliquota_ibs_municipal' => $aliquotas?->ibsMunicipal,
        ]);

        $origem->itens()->orderBy('id')->chunkById(500, function ($itens) use ($simulacao): void {
            $agora = now();

            $simulacao->itens()->insert($itens->map(fn (SimulacaoItem $item): array => [
                'simulacao_id' => $simulacao->id,
                'produto_id'   => $item->produto_id,
                'origem_id'    => $item->id,
                'descricao'    => $item->descricao,
                'ncm'          => $item->ncm,
                'nbs'          => $item->nbs,
                'cst'          => $item->cst,
                'c_class_trib' => $item->c_class_trib,
                'base_calculo' => $item->base_calculo,
                'created_at'   => $agora,
                'updated_at'   => $agora,
            ])->all());
        });

        $simulacao->update(['total_itens' => $simulacao->itens()->count()]);

        return $simulacao;
    }
}
