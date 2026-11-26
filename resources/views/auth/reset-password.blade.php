<x-layouts.app titulo="Nova senha">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-semibold text-slate-900">Nova senha</h1>
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-campo nome="email" rotulo="E-mail" tipo="email" :valor="$request->string('email')->toString()" autocomplete="username" required />
            <x-campo nome="password" rotulo="Nova senha" tipo="password" autocomplete="new-password" required autofocus />
            <x-campo nome="password_confirmation" rotulo="Confirme a nova senha" tipo="password" autocomplete="new-password" required />
            <x-botao class="w-full">Salvar nova senha</x-botao>
        </form>
    </div>
</x-layouts.app>
