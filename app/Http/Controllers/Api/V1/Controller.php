<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use App\Models\User;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    protected function usuario(Request $request): User
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 401);

        return $usuario;
    }

    /**
     * Itens por página pedidos em `por_pagina`, entre 1 e 100.
     */
    protected function porPagina(Request $request, int $padrao = 25): int
    {
        return max(1, min(100, $request->integer('por_pagina', $padrao)));
    }
}
