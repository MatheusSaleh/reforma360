<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Schemas;

use App\Rules\Cnpj;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('razao_social')
                    ->label('Razão social')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cnpj')
                    ->label('CNPJ')
                    ->mask('99.999.999/9999-99')
                    ->stripCharacters(['.', '/', '-'])
                    ->rules([new Cnpj])
                    ->unique(ignoreRecord: true)
                    ->nullable(),
            ]);
    }
}
