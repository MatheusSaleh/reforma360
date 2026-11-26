<x-layouts.app titulo="Importações">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold text-slate-900">Importações</h1>
        <a href="{{ route('importacoes.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Importar planilha</a>
    </div>

    @if ($importacoes->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">
            Nenhuma importação ainda. <a href="{{ route('importacoes.create') }}" class="text-emerald-700 hover:underline">Importe sua primeira planilha</a>.
        </div>
    @else
        <div class="relative mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Arquivo</th>
                        <th class="px-4 py-3">Enviado em</th>
                        <th class="px-4 py-3">Situação</th>
                        <th class="px-4 py-3 text-right">Importadas</th>
                        <th class="px-4 py-3 text-right">Com erro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($importacoes as $importacao)
                        <tr>
                            <td class="px-4 py-3"><a href="{{ route('importacoes.show', $importacao) }}" class="font-medium text-emerald-700 hover:underline">{{ $importacao->nome_arquivo }}</a></td>
                            <td class="px-4 py-3">{{ $importacao->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">@include('importacoes._status', ['status' => $importacao->status])</td>
                            <td class="px-4 py-3 text-right">{{ $importacao->linhas_importadas }}</td>
                            <td class="px-4 py-3 text-right">{{ $importacao->linhas_com_erro }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $importacoes->links() }}</div>
    @endif
</x-layouts.app>
