<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Recurso de outra empresa responde 404, para não revelar que existe.
 */
trait VerificaEmpresa
{
    private function mesmaEmpresa(User $user, int $empresaId): Response
    {
        return $user->empresa_id === $empresaId
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
