<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Actions\Simulacao\IniciarLinhaDoTempo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulacao\IniciarLinhaDoTempoRequest;
use App\Models\Simulacao;
use App\Models\User;
use App\Services\Simulacao\LinhaDoTempoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Linha do tempo 2026→2033 de uma simulação (recurso único por simulação).
 */
class LinhaDoTempoController extends Controller
{
    public function create(Simulacao $simulacao): View
    {
        Gate::authorize('view', $simulacao);

        $atual = $simulacao->linhaDoTempo;

        return view('simulacoes.linha-do-tempo.create', [
            'simulacao' => $simulacao,
            'plenas'    => $atual?->aliquotasPlenas(),
            'sugeridas' => config('transicao.aliquotas_plenas_estimadas'),
        ]);
    }

    public function store(IniciarLinhaDoTempoRequest $request, Simulacao $simulacao, IniciarLinhaDoTempo $iniciar): RedirectResponse
    {
        Gate::authorize('view', $simulacao);

        $iniciar->handle($this->usuario($request), $simulacao, $request->aliquotasPlenas());

        return to_route('simulacoes.linha-do-tempo.show', $simulacao)->with('status', 'Linha do tempo iniciada: cada ano de 2026 a 2033 está sendo simulado.');
    }

    public function show(Request $request, Simulacao $simulacao, LinhaDoTempoService $service): View|RedirectResponse
    {
        Gate::authorize('view', $simulacao);

        $linha = $simulacao->linhaDoTempo;

        if ($linha === null) {
            return to_route('simulacoes.linha-do-tempo.create', $simulacao);
        }

        $this->usuario($request)->unreadNotifications()
            ->where('data->linha_do_tempo_id', $linha->id)
            ->update(['read_at' => now()]);

        $item = $request->filled('item') ? $simulacao->itens()->find($request->integer('item')) : null;
        $simulacoesDosAnos = $linha->simulacoesDosAnos()->get();

        return view('simulacoes.linha-do-tempo.show', [
            'simulacao' => $simulacao,
            'linha'     => $linha,
            'item'      => $item,
            'itens'     => $simulacao->itens()->orderBy('descricao')->get(['id', 'descricao']),
            'anos'      => $service->anos($linha, $item),
            'progresso' => [
                'total'       => $simulacoesDosAnos->sum('total_itens'),
                'processados' => $simulacoesDosAnos->sum('itens_processados'),
                'canais'      => $simulacoesDosAnos->map(fn (Simulacao $s): string => "simulacoes.{$s->id}")->values()->all(),
            ],
        ]);
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
