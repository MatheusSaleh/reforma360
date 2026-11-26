<?php

declare(strict_types=1);

namespace App\Http\Controllers\Importacao;

use App\Actions\Importacao\IniciarImportacao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Importacao\ImportarCatalogoRequest;
use App\Models\Importacao;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ImportacaoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Importacao::class);

        return view('importacoes.index', [
            'importacoes' => $this->usuario($request)->empresa->importacoes()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Importacao::class);

        return view('importacoes.create');
    }

    public function store(ImportarCatalogoRequest $request, IniciarImportacao $iniciar): RedirectResponse
    {
        Gate::authorize('create', Importacao::class);

        $importacao = $iniciar->handle($this->usuario($request), $request->arquivo());

        return to_route('importacoes.show', $importacao)->with('status', 'Planilha recebida. O processamento começou.');
    }

    public function show(Importacao $importacao): View
    {
        Gate::authorize('view', $importacao);

        return view('importacoes.show', [
            'importacao' => $importacao,
            'erros'      => $importacao->erros()->orderBy('linha')->orderBy('id')->paginate(50),
        ]);
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
