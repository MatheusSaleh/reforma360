<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmpresasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('razao_social')->label('Razão social')->searchable()->sortable(),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => $state === null ? '—' : (string) preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $state))
                    ->placeholder('—'),
                TextColumn::make('usuarios_count')->label('Usuários')->numeric()->sortable(),
                TextColumn::make('produtos_count')->label('Produtos')->numeric()->sortable(),
                TextColumn::make('simulacoes_count')->label('Simulações')->numeric()->sortable(),
                TextColumn::make('created_at')->label('Cadastro')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
