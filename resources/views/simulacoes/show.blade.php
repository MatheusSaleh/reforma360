@php
    use App\Enums\StatusSimulacao;
    use App\Support\Formatador;
@endphp
<x-layouts.app :titulo="'Simulação #'.$simulacao->id">
    <a href="{{ route('simulacoes.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Simulações</a>
    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-slate-900">Simulação #{{ $simulacao->id }}</h1>
        @include('simulacoes._status', ['status' => $simulacao->status])
    </div>
    @if ($simulacao->importacao)
        <p class="mt-1 text-sm text-slate-600">
            Itens da nota
            <a href="{{ route('importacoes.show', $simulacao->importacao) }}" class="text-emerald-700 hover:underline">{{ $simulacao->importacao->tipo_documento?->label() }} nº {{ $simulacao->importacao->numero_documento }}</a>
            ({{ $simulacao->importacao->nome_arquivo }}), com o valor de cada item na nota.
        </p>
    @endif
    <p class="mt-1 text-sm text-slate-600">
        Fato gerador em {{ $simulacao->data_fato_gerador->format('d/m/Y') }} · {{ $simulacao->uf->value }}, município {{ $simulacao->municipio }}
        @if ($simulacao->aliquota_cbs !== null)
            · alíquotas informadas: CBS {{ Formatador::percentual($simulacao->aliquotasNominais()->cbs) }},
            IBS {{ Formatador::percentual($simulacao->aliquotasNominais()->ibsEstadual) }} + {{ Formatador::percentual($simulacao->aliquotasNominais()->ibsMunicipal) }}
        @endif
    </p>

    @if ($simulacao->status->emAndamento())
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
            data-progresso-simulacao
            data-canal="simulacoes.{{ $simulacao->id }}"
            data-total="{{ $simulacao->total_itens }}">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700">Simulando os itens…</span>
                <span data-progresso-texto>{{ $simulacao->itens_processados }} de {{ $simulacao->total_itens }}</span>
            </div>
            <div class="mt-2 h-3 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Progresso da simulação"
                aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $simulacao->percentualConcluido() }}">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $simulacao->percentualConcluido() }}%" data-progresso-barra></div>
            </div>
            <p class="mt-2 text-xs text-slate-500" aria-live="polite" data-progresso-erros>
                {{ $simulacao->itens_com_erro > 0 ? $simulacao->itens_com_erro.' com erro até agora.' : '' }}
            </p>
            <p class="mt-3 text-xs text-slate-500">Pode sair desta página: você receberá um aviso quando terminar.</p>
        </div>
    @else
        <div class="mt-6 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            <strong>Simulação.</strong> Valores calculados pela calculadora oficial da Receita Federal
            @if ($simulacao->aliquota_cbs !== null)
                com as alíquotas nominais informadas (resultado <code>CALCULO_SIMULADO</code>), que ainda não são as definitivas.
            @else
                com as alíquotas de referência de {{ $simulacao->data_fato_gerador->year }}.
            @endif
            Não substitui a orientação de um contador.
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
            @if ($xmlDoGrupo && $xmlDoGrupo['tipo'] && $simulacao->itens_processados > $simulacao->itens_com_erro)
                <a href="{{ route('simulacoes.xml-ibscbs', $simulacao) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Baixar XML do grupo IBS/CBS ({{ $xmlDoGrupo['tipo']->label() }})
                </a>
                <span class="text-xs text-slate-500">Gerado pela calculadora e validado no XSD da Receita. Inclui só os itens calculados.</span>
            @elseif ($xmlDoGrupo && $xmlDoGrupo['motivo'])
                <span class="text-xs text-slate-500">XML do grupo IBS/CBS indisponível: {{ $xmlDoGrupo['motivo'] }}</span>
            @endif
        </div>
        @error('simulacao')
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror
        @error('xml')
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        <dl class="mt-6 grid gap-4 sm:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <dt class="text-xs uppercase text-slate-500">Itens simulados</dt>
                <dd class="mt-1 text-2xl font-semibold">{{ $simulacao->itens_processados - $simulacao->itens_com_erro }} <span class="text-sm font-normal text-slate-500">de {{ $simulacao->total_itens }}</span></dd>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <dt class="text-xs uppercase text-slate-500">Base de cálculo</dt>
                <dd class="mt-1 text-xl font-semibold">{{ $simulacao->total_base_calculo !== null ? Formatador::moeda($simulacao->total_base_calculo) : '—' }}</dd>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <dt class="text-xs uppercase text-slate-500">CBS · IBS</dt>
                <dd class="mt-1 text-sm font-semibold">
                    {{ $simulacao->total_cbs !== null ? Formatador::moeda($simulacao->total_cbs) : '—' }} ·
                    {{ $simulacao->total_ibs !== null ? Formatador::moeda($simulacao->total_ibs) : '—' }}
                </dd>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <dt class="text-xs uppercase text-slate-500">Total de tributos</dt>
                <dd class="mt-1 text-xl font-semibold text-emerald-700">{{ $simulacao->total_tributos !== null ? Formatador::moeda($simulacao->total_tributos) : '—' }}</dd>
            </div>
        </dl>
    @endif

    <section class="mt-8" aria-labelledby="titulo-itens">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="titulo-itens" class="text-lg font-semibold text-slate-900">Itens</h2>
            @if ($simulacao->itens_com_erro > 0)
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('simulacoes.show', $simulacao) }}" @class(['font-semibold text-slate-900' => ! $somenteErros, 'text-emerald-700 hover:underline' => $somenteErros])>Todos</a>
                    <a href="{{ route('simulacoes.show', [$simulacao, 'erros' => 1]) }}" @class(['font-semibold text-slate-900' => $somenteErros, 'text-emerald-700 hover:underline' => ! $somenteErros])>Só com erro ({{ $simulacao->itens_com_erro }})</a>
                </div>
            @endif
        </div>
        <div class="relative mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3">NCM / NBS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Base</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Redução</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">CBS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">IBS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($itens as $item)
                        <tr @class(['bg-red-50/40' => $item->temErro()])>
                            <td class="px-4 py-2">
                                <span class="font-medium text-slate-900">{{ $item->descricao }}</span>
                                @if ($item->estado === null)
                                    <span class="block text-xs text-slate-500">aguardando</span>
                                @elseif ($item->temErro())
                                    @foreach ($item->erros ?? [] as $erro)
                                        <span class="block text-xs text-red-700">{{ $erro['codigo'] ? $erro['codigo'].': ' : '' }}{{ $erro['mensagem'] }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="px-4 py-2 font-mono">{{ $item->ncm ?? $item->nbs }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ Formatador::moeda($item->base_calculo) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $item->percentual_reducao !== null && bccomp($item->percentual_reducao, '0', 4) > 0 ? Formatador::percentual(rtrim(rtrim($item->percentual_reducao, '0'), '.')) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $item->valor_cbs !== null ? Formatador::moeda($item->valor_cbs) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $item->valor_ibs !== null ? Formatador::moeda($item->valor_ibs) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right font-medium">{{ $item->total_tributos !== null ? Formatador::moeda($item->total_tributos) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $itens->links() }}</div>
    </section>
</x-layouts.app>
