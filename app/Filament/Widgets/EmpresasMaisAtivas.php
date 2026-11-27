<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Empresa;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Empresas com mais simulações nos últimos 30 dias.
 */
class EmpresasMaisAtivas extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $desde = now()->subDays(30);

        return $table
            ->heading('Empresas mais ativas (últimos 30 dias)')
            ->query(fn (): Builder => Empresa::query()
                ->withCount([
                    'simulacoes as simulacoes_30_dias'   => fn (Builder $q) => $q->whereNull('linha_do_tempo_id')->where('created_at', '>=', $desde),
                    'importacoes as importacoes_30_dias' => fn (Builder $q) => $q->where('created_at', '>=', $desde),
                    'produtos',
                ])
                ->orderByDesc('simulacoes_30_dias')
                ->orderBy('razao_social'))
            ->paginated([10])
            ->columns([
                TextColumn::make('razao_social')->label('Empresa'),
                TextColumn::make('simulacoes_30_dias')->label('Simulações')->numeric(),
                TextColumn::make('importacoes_30_dias')->label('Importações')->numeric(),
                TextColumn::make('produtos_count')->label('Produtos')->numeric(),
            ]);
    }
}
