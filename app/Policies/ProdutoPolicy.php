<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Produto;
use App\Models\User;
use App\Policies\Concerns\VerificaEmpresa;
use Illuminate\Auth\Access\Response;

/**
 * Cada usuário só enxerga e altera os produtos da própria empresa.
 */
class ProdutoPolicy
{
    use VerificaEmpresa;

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
        return $this->mesmaEmpresa($user, $produto->empresa_id);
    }

    public function delete(User $user, Produto $produto): Response
    {
        return $this->mesmaEmpresa($user, $produto->empresa_id);
    }
}
