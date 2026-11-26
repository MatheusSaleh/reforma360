@php
    use App\Support\Formatador;

    $emAndamento = $linha->status->emAndamento();
    $percentual = $progresso['total'] > 0 ? intdiv($progresso['processados'] * 100, $progresso['total']) : 0;
@endphp
<x-layouts.app :titulo="'Linha do tempo · Simulação #'.$simulacao->id">
    <a href="{{ route('simulacoes.show', $simulacao) }}" class="text-sm text-slate-600 hover:text-slate-900">← Simulação #{{ $simulacao->id }}</a>
    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-slate-900">Linha do tempo 2026 → 2033</h1>
        @include('simulacoes._status', ['status' => $linha->status])
    </div>
    <p class="mt-1 text-sm text-slate-600">
        Simulação #{{ $simulacao->id }} · {{ $simulacao->uf->value }}, município {{ $simulacao->municipio }} ·
        alíquotas plenas estimadas: CBS {{ Formatador::percentual($linha->aliquotasPlenas()->cbs) }},
        IBS {{ Formatador::percentual($linha->aliquotasPlenas()->ibsEstadual) }} + {{ Formatador::percentual($linha->aliquotasPlenas()->ibsMunicipal) }}
        · <a href="{{ route('simulacoes.linha-do-tempo.create', $simulacao) }}" class="text-emerald-700 hover:underline">refazer com outras alíquotas</a>
    </p>

    @if ($emAndamento)
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
            data-progresso-simulacao
            data-canais='@json($progresso['canais'])'
            data-total="{{ $progresso['total'] }}"
            data-processados="{{ $progresso['processados'] }}">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700">Simulando os 8 anos…</span>
                <span data-progresso-texto>{{ $progresso['processados'] }} de {{ $progresso['total'] }}</span>
            </div>
            <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Progresso da linha do tempo"
                aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentual }}">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $percentual }}%" data-progresso-barra></div>
            </div>
            <p class="mt-2 text-xs text-slate-500" aria-live="polite" data-progresso-erros></p>
            <p class="mt-3 text-xs text-slate-500">Pode sair desta página: você receberá um aviso quando terminar.</p>
        </div>
    @else
        <div class="mt-6 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            <strong>Simulação.</strong> 2026 usa as alíquotas de referência da Receita. De 2027 em diante, os valores usam o cronograma
            da transição sobre alíquotas plenas <strong>estimadas</strong> (resultado <code>CALCULO_SIMULADO</code>) e mudam quando as
            alíquotas definitivas forem fixadas. Não substitui a orientação de um contador.
        </div>

        <form method="GET" action="{{ route('simulacoes.linha-do-tempo.show', $simulacao) }}" class="mt-6 flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Ver</span>
                <select name="item" class="w-72 max-w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm" onchange="this.form.submit()">
                    <option value="">Total da simulação</option>
                    @foreach ($itens as $opcao)
                        <option value="{{ $opcao->id }}" @selected($item?->id === $opcao->id)>{{ $opcao->descricao }}</option>
                    @endforeach
                </select>
            </label>
            <noscript><button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Ver</button></noscript>
        </form>

        <x-grafico-colunas-empilhadas class="mt-4"
            :titulo="($item ? $item->descricao : 'Total da simulação').' — CBS e IBS por ano'"
            :series="['CBS', 'IBS']"
            :colunas="collect($anos)->map(fn ($a) => ['rotulo' => (string) $a->ano, 'valores' => [$a->cbs, $a->ibs]])->all()" />

        <div class="relative mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">Valores por ano</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Ano</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Alíquotas nominais</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Base</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">CBS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">IBS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Total</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Carga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($anos as $ano)
                        <tr>
                            <td class="px-4 py-2 font-medium">
                                <a href="{{ route('simulacoes.show', $ano->simulacaoId) }}" class="text-emerald-700 hover:underline">{{ $ano->ano }}</a>
                                @if ($ano->erro)
                                    <span class="block text-xs font-normal text-red-700">{{ $ano->erro }}</span>
                                @elseif (! $item && $ano->itensComErro > 0)
                                    <span class="block text-xs font-normal text-amber-700">{{ $ano->itensComErro }} {{ $ano->itensComErro === 1 ? 'item' : 'itens' }} com erro</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-xs text-slate-600">
                                @if ($ano->aliquotas)
                                    CBS {{ Formatador::percentual($ano->aliquotas->cbs) }} · IBS {{ Formatador::percentual((string) $ano->aliquotaIbsTotal()) }}
                                @else
                                    referência da Receita
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $ano->base !== null ? Formatador::moeda($ano->base) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $ano->cbs !== null ? Formatador::moeda($ano->cbs) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $ano->ibs !== null ? Formatador::moeda($ano->ibs) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right font-medium">{{ $ano->total !== null ? Formatador::moeda($ano->total) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $ano->carga !== null ? Formatador::percentual($ano->carga) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-slate-500">Carga = CBS + IBS sobre a base de cálculo. Clique no ano para ver o detalhe por item.</p>
    @endif
</x-layouts.app>
