<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Actions\Simulacao\IniciarSimulacaoEmLote;
use App\Enums\Uf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulacao\IniciarSimulacaoRequest;
use App\Models\Simulacao;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SimulacaoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Simulacao::class);

        return view('simulacoes.index', [
            'simulacoes' => $this->usuario($request)->empresa->simulacoes()->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Simulacao::class);

        return view('simulacoes.create', [
            'ufs'           => Uf::cases(),
            'totalProdutos' => $this->usuario($request)->empresa->produtos()->count(),
        ]);
    }

    public function store(IniciarSimulacaoRequest $request, IniciarSimulacaoEmLote $iniciar): RedirectResponse
    {
        Gate::authorize('create', Simulacao::class);

        $simulacao = $iniciar->handle($this->usuario($request), $request->parametros());

        return to_route('simulacoes.show', $simulacao)->with('status', 'Simulação iniciada. Acompanhe o progresso abaixo.');
    }

    public function show(Request $request, Simulacao $simulacao): View
    {
        Gate::authorize('view', $simulacao);

        $this->usuario($request)->unreadNotifications()
            ->where('data->simulacao_id', $simulacao->id)
            ->update(['read_at' => now()]);

        $somenteErros = $request->boolean('erros');

        return view('simulacoes.show', [
            'simulacao'    => $simulacao,
            'somenteErros' => $somenteErros,
            'itens'        => $simulacao->itens()
                ->when($somenteErros, fn ($query) => $query->whereNotIn('estado', ['CALCULADO', 'CALCULO_SIMULADO']))
                ->orderBy('descricao')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
