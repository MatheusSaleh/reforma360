<?php

declare(strict_types=1);

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use Filament\Resources\Pages\EditRecord;

/**
 * Sem exclusão: a empresa tem usuários e o histórico de simulações.
 */
class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;
}
