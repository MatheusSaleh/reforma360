<?php

declare(strict_types=1);

namespace App\Actions\Simulacao;

use App\Enums\StatusSimulacao;
use App\Jobs\SimularItensJob;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\User;
use App\Services\Simulacao\Data\ParametrosSimulacao;
use App\Services\Simulacao\SimularItemService;
use Illuminate\Bus\Batch;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cria a simulação com um retrato do catálogo e despacha o lote de jobs
 * (um job por bloco de itens = uma chamada à calculadora).
 */
final readonly class IniciarSimulacaoEmLote
{
    public function __construct(
        private SimularItemService $simulacaoDeItem,
        #[Config('simulacao.itens_por_chamada')]
        private int $itensPorChamada = 50,
    ) {}

    /**
     * @throws ValidationException quando a empresa não tem produtos
     */
    public function handle(User $usuario, ParametrosSimulacao $parametros): Simulacao
    {
        $empresa = $usuario->empresa;

        if (! $empresa->produtos()->exists()) {
            throw ValidationException::withMessages(['simulacao' => 'Cadastre ou importe produtos antes de simular o catálogo.']);
        }

        $aliquotas = $this->simulacaoDeItem->exigeAliquotasNominais($parametros->data->year) ? $parametros->aliquotasNominais : null;

        $simulacao = DB::transaction(function () use ($empresa, $usuario, $parametros, $aliquotas): Simulacao {
            $simulacao = $empresa->simulacoes()->create([
                'user_id'                => $usuario->id,
                'status'                 => StatusSimulacao::Pendente,
                'data_fato_gerador'      => $parametros->data->toDateString(),
                'uf'                     => $parametros->uf,
                'municipio'              => $parametros->municipio,
                'aliquota_cbs'           => $aliquotas?->cbs,
                'aliquota_ibs_estadual'  => $aliquotas?->ibsEstadual,
                'aliquota_ibs_municipal' => $aliquotas?->ibsMunicipal,
            ]);

            $empresa->produtos()->orderBy('id')->chunkById(500, function ($produtos) use ($simulacao): void {
                $agora = now();

                $simulacao->itens()->insert($produtos->map(fn (Produto $produto): array => [
                    'simulacao_id' => $simulacao->id,
                    'produto_id'   => $produto->id,
                    'descricao'    => $produto->descricao,
                    'ncm'          => $produto->ncm,
                    'nbs'          => $produto->nbs,
                    'cst'          => $produto->cst,
                    'c_class_trib' => $produto->c_class_trib,
                    'base_calculo' => $produto->preco,
                    'created_at'   => $agora,
                    'updated_at'   => $agora,
                ])->all());
            });

            $simulacao->update(['total_itens' => $simulacao->itens()->count()]);

            return $simulacao;
        });

        $this->despacharLote($simulacao);

        return $simulacao;
    }

    private function despacharLote(Simulacao $simulacao): void
    {
        $jobs = [];

        $simulacao->itens()->orderBy('id')->select('id')->chunkById($this->itensPorChamada, function ($itens) use ($simulacao, &$jobs): void {
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
