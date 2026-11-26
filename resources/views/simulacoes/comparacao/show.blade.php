@php
    use App\Support\Formatador;

    $diferenca = $comparacao->diferenca();
    $simplesMenor = bccomp($diferenca, '0', 2) > 0;
    $empate = bccomp($diferenca, '0', 2) === 0;
    $absoluto = ltrim($diferenca, '-');
@endphp
<x-layouts.app :titulo="'Simples × regime regular · Simulação #'.$simulacao->id">
    <a href="{{ route('simulacoes.show', $simulacao) }}" class="text-sm text-slate-600 hover:text-slate-900">← Simulação #{{ $simulacao->id }}</a>
    <h1 class="mt-2 text-2xl font-semibold text-slate-900">Simples Nacional × regime regular</h1>
    <p class="mt-1 text-sm text-slate-600">
        Simulação #{{ $simulacao->id }} · {{ $simulacao->data_fato_gerador->format('d/m/Y') }} · atividade {{ $comparacao->atividade }} ·
        Simples: CBS {{ Formatador::percentual(rtrim(rtrim($comparacao->aliquota_cbs_simples, '0'), '.')) }} e IBS {{ Formatador::percentual(rtrim(rtrim($comparacao->aliquota_ibs_simples, '0'), '.')) }}
        · <a href="{{ route('simulacoes.comparacao.create', $simulacao) }}" class="text-emerald-700 hover:underline">refazer</a>
    </p>

    <div class="mt-6 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        <strong>Simulação simplificada.</strong> Compara só IBS e CBS dos {{ $comparacao->itens_comparados }} itens calculados nos dois regimes.
        O crédito das compras é uma estimativa (compras × alíquotas nominais do ano). A escolha do regime envolve outros tributos
        (ICMS, ISS, IRPJ, CSLL, contribuição previdenciária) e o efeito do crédito para os seus clientes. Converse com o seu contador.
    </div>

    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Simples Nacional</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ Formatador::moeda($comparacao->totalSimples()) }}</dd>
            <dd class="mt-1 text-xs text-slate-500">CBS {{ Formatador::moeda($comparacao->simples_cbs) }} · IBS {{ Formatador::moeda($comparacao->simples_ibs) }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Regime regular</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ Formatador::moeda($comparacao->liquidoRegular()) }}</dd>
            <dd class="mt-1 text-xs text-slate-500">
                Débitos {{ Formatador::moeda($comparacao->debitosRegular()) }}
                @if ($comparacao->compras !== null) − crédito estimado {{ Formatador::moeda($comparacao->credito_estimado) }} @endif
            </dd>
            @if ($comparacao->sobraCredito())
                <dd class="mt-1 text-xs text-amber-700">O crédito estimado passa dos débitos: sobraria saldo credor.</dd>
            @endif
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Diferença</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ Formatador::moeda($absoluto) }}</dd>
            <dd class="mt-1 text-xs text-slate-600">
                @if ($empate)
                    Os dois regimes dão o mesmo valor.
                @elseif ($simplesMenor)
                    a menos no <strong>Simples Nacional</strong>
                @else
                    a menos no <strong>regime regular</strong>
                @endif
            </dd>
        </div>
    </dl>

    @if ($comparacao->compras === null)
        <p class="mt-3 text-xs text-slate-500">Sem o valor das compras, o regime regular aparece sem o crédito que a empresa tomaria; informe-o em "refazer" para uma comparação mais justa.</p>
    @endif

    <section class="mt-8" aria-labelledby="titulo-itens-comparacao">
        <h2 id="titulo-itens-comparacao" class="text-lg font-semibold text-slate-900">Por item</h2>
        <div class="relative mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Produto</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Base</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Regime regular</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Simples</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Diferença</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($itens as $linha)
                        @php
                            $item = $linha->simulacaoItem;
                            $regular = bcadd($item->valor_cbs ?? '0', $item->valor_ibs ?? '0', 2);
                            $simples = $linha->totalSimples();
                        @endphp
                        <tr @class(['bg-slate-50/60' => ! $linha->foiComparado()])>
                            <td class="px-4 py-2">
                                <span class="font-medium text-slate-900">{{ $item->descricao }}</span>
                                @if (! $linha->foiComparado())
                                    @foreach ($linha->erros ?? [] as $erro)
                                        <span class="block text-xs text-slate-600">Não comparado: {{ $erro['codigo'] ? $erro['codigo'].': ' : '' }}{{ $erro['mensagem'] }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ Formatador::moeda($item->base_calculo) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ Formatador::moeda($regular) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $simples !== null ? Formatador::moeda($simples) : '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right">{{ $simples !== null ? (bccomp($regular, $simples, 2) >= 0 ? '' : '−').Formatador::moeda(ltrim(bcsub($regular, $simples, 2), '-')) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-slate-500">Diferença por item = regime regular (sem crédito) − Simples. Negativo: o item paga menos no regime regular.</p>
        <div class="mt-4">{{ $itens->links() }}</div>
    </section>
</x-layouts.app>
