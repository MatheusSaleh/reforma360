<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->hidden(fn (User $record): bool => $record->is(auth()->user())),
        ];
    }

    /**
     * is_admin não é mass assignable: gravado explicitamente. O próprio admin não
     * consegue se rebaixar (o campo vem desabilitado e é ignorado aqui).
     *
     * @param  User  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): User
    {
        $record->fill(array_intersect_key($data, array_flip(['name', 'email', 'empresa_id'])));

        if (array_key_exists('is_admin', $data) && ! $record->is(auth()->user())) {
            $record->forceFill(['is_admin' => (bool) $data['is_admin']]);
        }

        $record->save();

        return $record;
    }
}
