<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nome')->required()->maxLength(255),
                TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'razao_social')
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('is_admin')
                    ->label('Administrador da plataforma')
                    ->helperText('Acesso a este painel e ao Horizon.')
                    ->disabled(fn (?User $record): bool => $record !== null && $record->is(auth()->user())),
            ]);
    }
}
