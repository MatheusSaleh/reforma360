<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Simulacao\CompararRegimesRequest;
use App\Models\Simulacao;
use App\Models\User;
use App\Services\Simulacao\CompararRegimesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Comparação Simples Nacional × regime regular de uma simulação (recurso único por simulação).
 */
class ComparacaoRegimeController extends Controller
{
    public function create(Simulacao $simulacao, CompararRegimesService $service): View
    {
        Gate::authorize('view', $simulacao);

        $impedimento = $service->impedimento($simulacao);

        return view('simulacoes.comparacao.create', [
            'simulacao'   => $simulacao,
            'impedimento' => $impedimento,
            'atividades'  => $impedimento === null ? $service->atividades($simulacao) : [],
            'atual'       => $simulacao->comparacao,
        ]);
    }

    public function store(CompararRegimesRequest $request, Simulacao $simulacao, CompararRegimesService $service): RedirectResponse
    {
        Gate::authorize('view', $simulacao);

        $service->comparar($this->usuario($request), $simulacao, $request->parametros());

        return to_route('simulacoes.comparacao.show', $simulacao);
    }

    public function show(Request $request, Simulacao $simulacao): View|RedirectResponse
    {
        Gate::authorize('view', $simulacao);

        $comparacao = $simulacao->comparacao;

        if ($comparacao === null) {
            return to_route('simulacoes.comparacao.create', $simulacao);
        }

        return view('simulacoes.comparacao.show', [
            'simulacao'  => $simulacao,
            'comparacao' => $comparacao,
            'itens'      => $comparacao->itens()->with('simulacaoItem')->orderBy('id')->paginate(25)->withQueryString(),
        ]);
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
