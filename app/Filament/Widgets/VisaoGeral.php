<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\StatusImportacao;
use App\Enums\StatusSimulacao;
use App\Models\Empresa;
use App\Models\FailedJob;
use App\Models\Importacao;
use App\Models\Produto;
use App\Models\Simulacao;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Métricas de uso da plataforma (últimos 30 dias onde indicado).
 */
class VisaoGeral extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Uso da plataforma';

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        $desde = now()->subDays(30);
        $simulacoes = Simulacao::query()->whereNull('linha_do_tempo_id')->where('created_at', '>=', $desde);
        $importacoes = Importacao::query()->where('created_at', '>=', $desde);
        $falhas = FailedJob::query()->count();

        return [
            Stat::make('Empresas', Empresa::query()->count())
                ->description(Empresa::query()->where('created_at', '>=', $desde)->count().' novas em 30 dias'),
            Stat::make('Usuários', User::query()->count())
                ->description(trans_choice(':count administrador|:count administradores', User::query()->where('is_admin', true)->count())),
            Stat::make('Produtos cadastrados', Produto::query()->count()),
            Stat::make('Simulações em 30 dias', (clone $simulacoes)->count())
                ->description((clone $simulacoes)->whereIn('status', [StatusSimulacao::ConcluidaComErros, StatusSimulacao::Falhou])->count().' com erros ou falha'),
            Stat::make('Importações em 30 dias', (clone $importacoes)->count())
                ->description((clone $importacoes)->where('status', StatusImportacao::Falhou)->count().' falharam'),
            Stat::make('Jobs com falha', $falhas)
                ->description($falhas > 0 ? 'Veja em Operação → Jobs com falha' : 'Nenhuma falha')
                ->color($falhas > 0 ? 'danger' : 'success'),
        ];
    }
}
