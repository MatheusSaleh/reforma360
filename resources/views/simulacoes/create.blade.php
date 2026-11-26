<x-layouts.app titulo="Nova simulação">
    @if ($importacao)
        <h1 class="text-2xl font-semibold text-slate-900">Simular a nota</h1>
        <p class="mt-1 text-sm text-slate-600">
            Os {{ $totalProdutos }} itens de {{ $importacao->tipo_documento?->label() ?? 'arquivo' }} nº {{ $importacao->numero_documento }} ({{ $importacao->nome_arquivo }})
            serão simulados com o valor de cada item na nota. UF e município vêm da nota; confira a data e as alíquotas.
        </p>
    @else
        <h1 class="text-2xl font-semibold text-slate-900">Simular o catálogo</h1>
        <p class="mt-1 text-sm text-slate-600">
            Os {{ $totalProdutos }} produtos cadastrados serão simulados com os parâmetros abaixo. O processamento acontece em segundo plano e você será avisado ao final.
        </p>
    @endif

    @error('simulacao')
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
    @enderror

    @if ($totalProdutos === 0)
        <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">
            Você ainda não tem produtos. <a href="{{ route('produtos.create') }}" class="text-emerald-700 hover:underline">Cadastre</a>
            ou <a href="{{ route('importacoes.create') }}" class="text-emerald-700 hover:underline">importe uma planilha</a>.
        </div>
    @else
        <form method="POST" action="{{ route('simulacoes.store') }}" class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm" novalidate>
            @csrf
            @if ($importacao)
                <input type="hidden" name="importacao_id" value="{{ $importacao->id }}">
            @endif
            @include('simulacoes._parametros', ['ufs' => $ufs])
            <x-botao>Simular {{ $totalProdutos }} {{ $importacao ? ($totalProdutos === 1 ? 'item' : 'itens') : ($totalProdutos === 1 ? 'produto' : 'produtos') }}</x-botao>
        </form>
    @endif
</x-layouts.app>
