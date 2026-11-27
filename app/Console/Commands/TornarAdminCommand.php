<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Promove (ou rebaixa, com --remover) um usuário a administrador da plataforma.
 */
#[Signature('reforma360:tornar-admin {email : E-mail do usuário} {--remover : Remove o acesso de administrador}')]
#[Description('Dá ou remove o acesso de administrador (/admin e /horizon) a um usuário')]
class TornarAdminCommand extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $usuario = User::query()->where('email', $email)->first();

        if ($usuario === null) {
            $this->error("Nenhum usuário com o e-mail {$email}.");

            return self::FAILURE;
        }

        $admin = ! $this->option('remover');
        $usuario->forceFill(['is_admin' => $admin])->save();

        $this->info($admin ? "{$email} agora é administrador." : "{$email} não é mais administrador.");

        return self::SUCCESS;
    }
}
