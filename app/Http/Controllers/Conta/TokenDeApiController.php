<?php

declare(strict_types=1);

namespace App\Http\Controllers\Conta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conta\CriarTokenDeApiRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Tokens da API pública. Cada token pertence ao usuário e opera na empresa dele.
 */
class TokenDeApiController extends Controller
{
    public function index(Request $request): View
    {
        return view('conta.tokens.index', [
            'tokens'     => $this->usuario($request)->tokens()->latest()->get(),
            'permissoes' => config('api.permissoes'),
        ]);
    }

    public function store(CriarTokenDeApiRequest $request): RedirectResponse
    {
        $token = $this->usuario($request)->createToken($request->nome(), $request->permissoes());

        return to_route('conta.tokens.index')
            ->with('status', 'Token criado. Copie agora: ele não será mostrado de novo.')
            ->with('token_criado', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token): RedirectResponse
    {
        $this->usuario($request)->tokens()->whereKey($token)->firstOrFail()->delete();

        return to_route('conta.tokens.index')->with('status', 'Token revogado. Integrações que o usavam deixam de funcionar.');
    }

    private function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }
}
