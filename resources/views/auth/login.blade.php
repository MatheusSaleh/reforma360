<x-layouts.app titulo="Entrar">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-slate-900">Entrar</h1>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <x-campo nome="email" rotulo="E-mail" tipo="email" autocomplete="username" required autofocus />
            <x-campo nome="password" rotulo="Senha" tipo="password" autocomplete="current-password" required />
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> Manter conectado
            </label>
            <div class="flex items-center justify-between">
                <a href="{{ route('password.request') }}" class="text-sm text-emerald-700 hover:underline">Esqueci minha senha</a>
                <x-botao>Entrar</x-botao>
            </div>
        </form>
        <p class="mt-4 text-sm text-slate-600">Ainda não tem conta? <a href="{{ route('register') }}" class="text-emerald-700 hover:underline">Crie uma</a>.</p>
    </div>
</x-layouts.app>
