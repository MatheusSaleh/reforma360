<?php

declare(strict_types=1);

use App\Models\Simulacao;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
| Progresso da simulação em lote: só usuários da empresa dona da simulação.
*/
Broadcast::channel('simulacoes.{simulacao}', fn (User $user, Simulacao $simulacao): bool => $user->empresa_id === $simulacao->empresa_id);
