<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Importacao;
use App\Models\User;
use App\Policies\Concerns\VerificaEmpresa;
use Illuminate\Auth\Access\Response;

class ImportacaoPolicy
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

    public function view(User $user, Importacao $importacao): Response
    {
        return $this->mesmaEmpresa($user, $importacao->empresa_id);
    }
}
