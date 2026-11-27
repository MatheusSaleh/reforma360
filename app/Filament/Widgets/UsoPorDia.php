<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Importacao;
use App\Models\Simulacao;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

/**
 * Simulações e importações por dia nos últimos 30 dias. Cores da paleta validada
 * (skill dataviz): azul #257de0 e laranja #f47230; linhas de 2px.
 */
class UsoPorDia extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Simulações e importações por dia (últimos 30 dias)';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '260px';

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $inicio = CarbonImmutable::today()->subDays(29);
        $dias = collect(range(0, 29))->map(fn (int $i): CarbonImmutable => $inicio->addDays($i));

        $contar = function (string $modelo) use ($inicio): array {
            $query = $modelo::query()->where('created_at', '>=', $inicio);

            if ($modelo === Simulacao::class) {
                $query->whereNull('linha_do_tempo_id');
            }

            return $query->get(['created_at'])
                ->countBy(fn ($registro): string => $registro->created_at->toDateString())
                ->all();
        };

        $simulacoes = $contar(Simulacao::class);
        $importacoes = $contar(Importacao::class);

        return [
            'datasets' => [
                [
                    'label'           => 'Simulações',
                    'data'            => $dias->map(fn (CarbonImmutable $d): int => $simulacoes[$d->toDateString()] ?? 0)->all(),
                    'borderColor'     => '#257de0',
                    'backgroundColor' => '#257de0',
                    'borderWidth'     => 2,
                    'pointRadius'     => 3,
                ],
                [
                    'label'           => 'Importações',
                    'data'            => $dias->map(fn (CarbonImmutable $d): int => $importacoes[$d->toDateString()] ?? 0)->all(),
                    'borderColor'     => '#f47230',
                    'backgroundColor' => '#f47230',
                    'borderWidth'     => 2,
                    'pointRadius'     => 3,
                ],
            ],
            'labels' => $dias->map(fn (CarbonImmutable $d): string => $d->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales'  => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => true]],
        ];
    }
}
