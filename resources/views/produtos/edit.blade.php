<x-layouts.app titulo="Editar produto">
    <h1 class="text-2xl font-semibold text-slate-900">Editar {{ $produto->descricao }}</h1>
    <form method="POST" action="{{ route('produtos.update', $produto) }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('produtos._form')
    </form>
</x-layouts.app>
