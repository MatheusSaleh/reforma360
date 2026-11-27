@props(['colunas', 'series', 'titulo', 'unidade' => 'R$'])
{{--
    Colunas empilhadas (2 séries) em SVG gerado no servidor. Paleta validada (dataviz):
    série 1 azul #257de0, série 2 laranja #f47230, série 3 roxa #7c3aed; 2px de superfície entre segmentos;
    topo arredondado (4px), base reta; tooltip nativo por coluna; tabela ao lado.
--}}
@php
    use App\Support\Formatador;
    use App\Support\GraficoColunas;

    $altura = 220;
    $topo = 16;
    $margemEsquerda = 64;
    $larguraColuna = 24;
    $passo = 64;
    $largura = $margemEsquerda + count($colunas) * $passo + 8;
    $cores = ['#257de0', '#f47230', '#7c3aed'];
    $grafico = GraficoColunas::montar($colunas, $altura);
    $base = $topo + $altura;
    $tick = fn (string $valor) => $unidade === 'R$'
        ? 'R$ '.number_format((int) bcadd($valor, '0', 0), 0, ',', '.')
        : Formatador::percentual($valor);
@endphp
<figure {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm']) }}>
    <figcaption class="flex flex-wrap items-center justify-between gap-2">
        <span class="text-sm font-semibold text-slate-900">{{ $titulo }}</span>
        <span class="flex items-center gap-4 text-xs text-slate-600" aria-hidden="true">
            @foreach ($series as $indice => $serie)
                <span class="flex items-center gap-1.5"><span class="inline-block h-2.5 w-2.5 rounded-sm" style="background: {{ $cores[$indice] }}"></span>{{ $serie }}</span>
            @endforeach
        </span>
    </figcaption>
    <div class="mt-3 overflow-x-auto">
        <svg viewBox="0 0 {{ $largura }} {{ $base + 28 }}" width="{{ $largura }}" height="{{ $base + 28 }}" role="img"
            aria-label="{{ $titulo }}: {{ collect($grafico['colunas'])->map(fn ($c) => $c['rotulo'].' '.($c['total'] !== null ? Formatador::moeda($c['total']) : 'sem resultado'))->implode('; ') }}"
            class="max-w-none font-sans">
            @foreach ($grafico['ticks'] as $valorTick)
                @php
                    $y = $base - (int) bcdiv(bcmul($valorTick, (string) $altura, 2), $grafico['teto'], 0);
                @endphp
                <line x1="{{ $margemEsquerda - 4 }}" x2="{{ $largura }}" y1="{{ $y }}" y2="{{ $y }}" stroke="#e5e5e5" stroke-width="1" />
                <text x="{{ $margemEsquerda - 8 }}" y="{{ $y + 4 }}" text-anchor="end" font-size="11" fill="#666666">{{ $tick($valorTick) }}</text>
            @endforeach
            @foreach ($grafico['colunas'] as $indice => $coluna)
                @php
                    $x = $margemEsquerda + $indice * $passo + intdiv($passo - $larguraColuna, 2);
                    $yAtual = $base;
                    $ultimoVisivel = collect($coluna['alturas'])->keys()->filter(fn ($i) => $coluna['alturas'][$i] > 0)->last();
                @endphp
                <g>
                    <title>{{ $coluna['rotulo'] }}: @foreach ($series as $s => $serie){{ $serie }} {{ $coluna['valores'][$s] !== null ? Formatador::moeda($coluna['valores'][$s]) : '—' }}{{ $loop->last ? '' : ' · ' }}@endforeach{{ $coluna['total'] !== null ? ' · Total '.Formatador::moeda($coluna['total']) : ' · sem resultado' }}</title>
                    <rect x="{{ $x - 8 }}" y="{{ $topo }}" width="{{ $larguraColuna + 16 }}" height="{{ $altura }}" fill="transparent" />
                    @foreach ($coluna['alturas'] as $s => $alturaSegmento)
                        @if ($alturaSegmento > 0)
                            @php
                                $alturaDesenho = max(1, $alturaSegmento - ($s === $ultimoVisivel ? 0 : 2));
                                $y = $yAtual - $alturaSegmento;
                                $yAtual = $y;
                            @endphp
                            @if ($s === $ultimoVisivel && $alturaDesenho > 4)
                                <path d="M{{ $x }},{{ $y + $alturaDesenho }} V{{ $y + 4 }} Q{{ $x }},{{ $y }} {{ $x + 4 }},{{ $y }} H{{ $x + $larguraColuna - 4 }} Q{{ $x + $larguraColuna }},{{ $y }} {{ $x + $larguraColuna }},{{ $y + 4 }} V{{ $y + $alturaDesenho }} Z" fill="{{ $cores[$s] }}" />
                            @else
                                <rect x="{{ $x }}" y="{{ $y + ($s === $ultimoVisivel ? 0 : 2) }}" width="{{ $larguraColuna }}" height="{{ $alturaDesenho }}" fill="{{ $cores[$s] }}" />
                            @endif
                        @endif
                    @endforeach
                    @if ($coluna['total'] === null)
                        <text x="{{ $x + intdiv($larguraColuna, 2) }}" y="{{ $base - 6 }}" text-anchor="middle" font-size="11" fill="#666666">—</text>
                    @endif
                    <text x="{{ $x + intdiv($larguraColuna, 2) }}" y="{{ $base + 18 }}" text-anchor="middle" font-size="12" fill="#191919">{{ $coluna['rotulo'] }}</text>
                </g>
            @endforeach
            <line x1="{{ $margemEsquerda - 4 }}" x2="{{ $largura }}" y1="{{ $base }}" y2="{{ $base }}" stroke="#a3a3a3" stroke-width="1" />
        </svg>
    </div>
</figure>
