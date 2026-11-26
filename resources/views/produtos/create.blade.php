<x-layouts.app titulo="Novo produto">
    <h1 class="text-2xl font-semibold text-slate-900">Novo produto ou serviço</h1>
    <form method="POST" action="{{ route('produtos.store') }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @include('produtos._form')
    </form>
</x-layouts.app>
