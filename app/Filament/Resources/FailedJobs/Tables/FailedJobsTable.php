<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs\Tables;

use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\HtmlString;

class FailedJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('failed_at', 'desc')
            ->emptyStateHeading('Nenhum job com falha')
            ->columns([
                TextColumn::make('failed_at')->label('Falhou em')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('queue')->label('Fila')->badge(),
                TextColumn::make('job')->label('Job')->state(fn (FailedJob $record): string => $record->nomeDoJob()),
                TextColumn::make('erro')->label('Erro')->state(fn (FailedJob $record): string => $record->resumoDoErro())->wrap(),
                TextColumn::make('uuid')->label('UUID')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            ])
            ->filters([
                SelectFilter::make('queue')->label('Fila')->options(fn (): array => FailedJob::query()->distinct()->orderBy('queue')->pluck('queue', 'queue')->all()),
            ])
            ->recordActions([
                Action::make('detalhes')
                    ->label('Detalhes')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->modalHeading(fn (FailedJob $record): string => $record->nomeDoJob())
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (FailedJob $record): HtmlString => new HtmlString(
                        '<p class="text-sm">Fila <strong>'.e($record->queue).'</strong> · UUID '.e($record->uuid).'</p>'
                        .'<pre class="mt-3 max-h-96 overflow-auto rounded bg-gray-950 p-3 text-xs text-gray-100">'.e($record->exception).'</pre>'
                    )),
                Action::make('reprocessar')
                    ->label('Reprocessar')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->action(fn (FailedJob $record) => self::reprocessar([$record->uuid])),
                Action::make('descartar')
                    ->label('Descartar')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (FailedJob $record) => self::descartar([$record->uuid])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('reprocessarSelecionados')
                        ->label('Reprocessar selecionados')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::reprocessar(self::uuids($records))),
                    BulkAction::make('descartarSelecionados')
                        ->label('Descartar selecionados')
                        ->icon(Heroicon::OutlinedTrash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::descartar(self::uuids($records))),
                ]),
            ]);
    }

    /**
     * @param  Collection<int, FailedJob>  $registros
     * @return list<string>
     */
    private static function uuids(Collection $registros): array
    {
        return array_values($registros->map(fn (FailedJob $job): string => $job->uuid)->all());
    }

    /**
     * @param  list<string>  $uuids
     */
    private static function reprocessar(array $uuids): void
    {
        Artisan::call('queue:retry', ['id' => $uuids]);

        Notification::make()->title(count($uuids) === 1 ? 'Job enviado de volta para a fila.' : count($uuids).' jobs enviados de volta para a fila.')->success()->send();
    }

    /**
     * @param  list<string>  $uuids
     */
    private static function descartar(array $uuids): void
    {
        foreach ($uuids as $uuid) {
            Artisan::call('queue:forget', ['id' => $uuid]);
        }

        Notification::make()->title(count($uuids) === 1 ? 'Job descartado.' : count($uuids).' jobs descartados.')->success()->send();
    }
}
