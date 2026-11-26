<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogo\ProdutoRequest;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProdutoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Produto::class);

        $busca = $request->string('busca')->trim()->toString();

        return view('produtos.index', [
            'busca'    => $busca,
            'produtos' => $this->usuario($request)->empresa->produtos()
                ->busca($busca)
                ->orderBy('descricao')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Produto::class);

        return view('produtos.create', ['produto' => new Produto]);
    }

    public function store(ProdutoRequest $request): RedirectResponse
    {
        Gate::authorize('create', Produto::class);

        $this->usuario($request)->empresa->produtos()->create($request->dadosDoProduto());

        return to_route('produtos.index')->with('status', 'Produto cadastrado.');
    }

    public function edit(Produto $produto): View
    {
        Gate::authorize('update', $produto);

        return view('produtos.edit', ['produto' => $produto]);
    }

    public function update(ProdutoRequest $request, Produto $produto): RedirectResponse
    {
        Gate::authorize('update', $produto);

        $produto->update($request->dadosDoProduto());

        return to_route('produtos.index')->with('status', 'Produto atualizado.');
    }

    public function destroy(Produto $produto): RedirectResponse
    {
        Gate::authorize('delete', $produto);

        $produto->delete();

        return to_route('produtos.index')->with('status', 'Produto excluído.');
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
