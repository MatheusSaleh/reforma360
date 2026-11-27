<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->sortable(),
                TextColumn::make('empresa.razao_social')->label('Empresa')->searchable()->sortable(),
                IconColumn::make('is_admin')->label('Admin')->boolean(),
                TextColumn::make('created_at')->label('Cadastro')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Administrador'),
                SelectFilter::make('empresa')->label('Empresa')->relationship('empresa', 'razao_social')->searchable()->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('enviarRedefinicaoDeSenha')
                    ->label('Enviar redefinição de senha')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $status = Password::sendResetLink(['email' => $record->email]);

                        Notification::make()
                            ->title($status === Password::RESET_LINK_SENT ? 'Link de redefinição enviado.' : (string) __($status))
                            ->{$status === Password::RESET_LINK_SENT ? 'success' : 'warning'}()
                            ->send();
                    }),
            ]);
    }
}
