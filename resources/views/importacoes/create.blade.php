<x-layouts.app titulo="Importar planilha">
    <h1 class="text-2xl font-semibold text-slate-900">Importar planilha de produtos</h1>
    <p class="mt-1 text-sm text-slate-600">Envie um arquivo CSV ou Excel (XLSX). Cada linha válida vira um produto; produtos com a mesma descrição são atualizados.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('importacoes.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            @csrf
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Arquivo</span>
                <input type="file" name="arquivo" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required
                    class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100">
                <span class="mt-1 block text-xs text-slate-500">
                    Até {{ number_format(config('importacao.tamanho_maximo_kb') / 1024, 0) }} MB e {{ number_format(config('importacao.maximo_linhas'), 0, ',', '.') }} linhas.
                </span>
                @error('arquivo')<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
            </label>
            <x-botao>Importar</x-botao>
        </form>

        <aside class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-700 shadow-sm">
            <h2 class="font-semibold text-slate-900">Planilha modelo</h2>
            <p class="mt-1">Baixe o modelo com o cabeçalho e exemplos:</p>
            <div class="mt-3 flex gap-2">
                <a href="{{ route('importacoes.modelo', 'xlsx') }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-50">Excel (.xlsx)</a>
                <a href="{{ route('importacoes.modelo', 'csv') }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium hover:bg-slate-50">CSV</a>
            </div>
            <h3 class="mt-5 font-semibold text-slate-900">Colunas</h3>
            <dl class="mt-2 space-y-1">
                <div><dt class="inline font-mono">descricao</dt> <dd class="inline text-slate-600">— nome do produto (obrigatório)</dd></div>
                <div><dt class="inline font-mono">ncm</dt> <dd class="inline text-slate-600">— 8 dígitos, para mercadorias</dd></div>
                <div><dt class="inline font-mono">nbs</dt> <dd class="inline text-slate-600">— 9 dígitos, para serviços (NCM ou NBS)</dd></div>
                <div><dt class="inline font-mono">cst</dt> <dd class="inline text-slate-600">— 3 dígitos</dd></div>
                <div><dt class="inline font-mono">c_class_trib</dt> <dd class="inline text-slate-600">— 6 dígitos, do CST informado</dd></div>
                <div><dt class="inline font-mono">preco</dt> <dd class="inline text-slate-600">— ex.: 1.200,00</dd></div>
            </dl>
            <p class="mt-3 text-xs text-slate-500">CSV com separador ";" ou ",", em UTF-8 ou no formato salvo pelo Excel. Os códigos são conferidos com as tabelas vigentes da Receita.</p>
        </aside>
    </div>
</x-layouts.app>
