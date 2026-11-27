<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Cria o usuário com senha aleatória e envia o link para ele definir a própria senha.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): User
    {
        $usuario = new User;
        $usuario->fill([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'empresa_id' => $data['empresa_id'],
            'password'   => Str::password(32),
        ]);
        $usuario->forceFill(['is_admin' => (bool) ($data['is_admin'] ?? false)])->save();

        Password::sendResetLink(['email' => $usuario->email]);

        return $usuario;
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Usuário criado. Enviamos o link para ele definir a senha.';
    }
}
