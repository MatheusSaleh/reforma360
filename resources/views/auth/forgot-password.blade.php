<x-layouts.app titulo="Redefinir senha">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-slate-900">Redefinir senha</h1>
        <p class="mt-1 text-sm text-slate-600">Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <x-campo nome="email" rotulo="E-mail" tipo="email" autocomplete="username" required autofocus />
            <x-botao class="w-full">Enviar link</x-botao>
        </form>
    </div>
</x-layouts.app>
