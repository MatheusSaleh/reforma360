<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs\Pages;

use App\Filament\Resources\FailedJobs\FailedJobResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('horizon')
                ->label('Abrir o Horizon')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url('/horizon')
                ->openUrlInNewTab(),
        ];
    }
}
