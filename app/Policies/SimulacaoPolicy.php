<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Simulacao;
use App\Models\User;
use App\Policies\Concerns\VerificaEmpresa;
use Illuminate\Auth\Access\Response;

class SimulacaoPolicy
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

    public function view(User $user, Simulacao $simulacao): Response
    {
        return $this->mesmaEmpresa($user, $simulacao->empresa_id);
    }
}
