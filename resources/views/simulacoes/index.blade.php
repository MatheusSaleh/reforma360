@php use App\Support\Formatador; @endphp
<x-layouts.app titulo="Simulações">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Simulações do catálogo</h1>
            <p class="mt-1 text-sm text-slate-600">Simule todos os produtos de uma vez e compare cenários (datas, locais e alíquotas).</p>
        </div>
        <a href="{{ route('simulacoes.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Nova simulação</a>
    </div>

    @if ($simulacoes->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">
            Nenhuma simulação ainda. <a href="{{ route('simulacoes.create') }}" class="text-emerald-700 hover:underline">Simule seu catálogo</a>.
        </div>
    @else
        <div class="relative mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Data do fato gerador</th>
                        <th class="px-4 py-3">Local</th>
                        <th class="px-4 py-3">Situação</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Itens</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">CBS + IBS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($simulacoes as $simulacao)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('simulacoes.show', $simulacao) }}" class="font-medium text-emerald-700 hover:underline">#{{ $simulacao->id }}</a>
                                @if ($simulacao->importacao_id)
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">nota</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $simulacao->data_fato_gerador->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $simulacao->uf->value }} · {{ $simulacao->municipio }}</td>
                            <td class="px-4 py-3">@include('simulacoes._status', ['status' => $simulacao->status])</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ $simulacao->total_itens }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ $simulacao->total_tributos !== null ? Formatador::moeda($simulacao->total_tributos) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $simulacoes->links() }}</div>
    @endif
</x-layouts.app>
