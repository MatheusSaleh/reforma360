<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Cada usuário só enxerga e altera os produtos da própria empresa.
 * Produto de outra empresa responde 404, para não revelar que existe.
 */
class ProdutoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Produto $produto): Response
    {
        return $this->mesmaEmpresa($user, $produto);
    }

    public function delete(User $user, Produto $produto): Response
    {
        return $this->mesmaEmpresa($user, $produto);
    }

    private function mesmaEmpresa(User $user, Produto $produto): Response
    {
        return $user->empresa_id === $produto->empresa_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
