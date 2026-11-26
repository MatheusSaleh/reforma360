<x-layouts.app titulo="Criar conta">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-slate-900">Criar conta</h1>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-slate-900">Empresa</legend>
                <x-campo nome="razao_social" rotulo="Razão social" required autofocus />
                <x-campo nome="cnpj" rotulo="CNPJ (opcional)" inputmode="numeric" placeholder="00.000.000/0000-00" />
            </fieldset>
            <fieldset class="space-y-4">
                <legend class="text-sm font-semibold text-slate-900">Seus dados</legend>
                <x-campo nome="name" rotulo="Nome" autocomplete="name" required />
                <x-campo nome="email" rotulo="E-mail" tipo="email" autocomplete="username" required />
                <x-campo nome="password" rotulo="Senha" tipo="password" autocomplete="new-password" required />
                <x-campo nome="password_confirmation" rotulo="Confirme a senha" tipo="password" autocomplete="new-password" required />
            </fieldset>
            <x-botao class="w-full">Criar conta</x-botao>
        </form>
        <p class="mt-4 text-sm text-slate-600">Já tem conta? <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Entrar</a>.</p>
    </div>
</x-layouts.app>
