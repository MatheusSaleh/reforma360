<x-layouts.app titulo="Produtos">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Produtos e serviços</h1>
            <p class="mt-1 text-sm text-slate-600">Catálogo de {{ auth()->user()->empresa->razao_social }}.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('importacoes.create') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Importar planilha</a>
            <a href="{{ route('produtos.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">Novo produto</a>
        </div>
    </div>

    <form method="GET" action="{{ route('produtos.index') }}" class="mt-6 flex gap-2" role="search">
        <input type="search" name="busca" value="{{ $busca }}" placeholder="Buscar por descrição, NCM ou NBS" aria-label="Buscar produtos"
            class="w-full max-w-md rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Buscar</button>
    </form>

    @if ($produtos->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">
            @if ($busca !== '')
                Nenhum produto encontrado para "{{ $busca }}".
            @else
                Nenhum produto cadastrado ainda. <a href="{{ route('produtos.create') }}" class="text-emerald-700 hover:underline">Cadastre o primeiro</a>.
            @endif
        </div>
    @else
        <div class="relative mt-6 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Descrição</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">NCM / NBS</th>
                        <th class="px-4 py-3">CST / cClassTrib</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right">Preço</th>
                        <th class="px-4 py-3"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($produtos as $produto)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $produto->descricao }}</td>
                            <td class="px-4 py-3">{{ $produto->tipo()->label() }}</td>
                            <td class="px-4 py-3 font-mono">{{ $produto->ncm ?? $produto->nbs }}</td>
                            <td class="px-4 py-3 font-mono">{{ $produto->cst }} / {{ $produto->c_class_trib }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ \App\Support\Formatador::moeda($produto->preco) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('produtos.edit', $produto) }}" class="text-emerald-700 hover:underline">Editar</a>
                                    <form method="POST" action="{{ route('produtos.destroy', $produto) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Excluir '.$produto->descricao.'?') }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $produtos->links() }}</div>
    @endif
</x-layouts.app>
