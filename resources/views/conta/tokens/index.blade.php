<x-layouts.app titulo="Tokens de API">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Tokens de API</h1>
            <p class="mt-1 text-sm text-slate-600">
                Integre seu sistema ao Reforma360. Os tokens agem em seu nome, no catálogo de {{ auth()->user()->empresa->razao_social }}.
            </p>
        </div>
        <a href="{{ url(config('scribe.laravel.docs_url')) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Documentação da API</a>
    </div>

    @if (session('token_criado'))
        <div class="mt-6 rounded-lg border border-amber-300 bg-amber-50 p-4" role="alert">
            <p class="text-sm font-semibold text-amber-900">Copie o token agora. Por segurança, ele não será mostrado de novo.</p>
            <div class="mt-3 flex gap-2">
                <input id="token-criado" type="text" readonly value="{{ session('token_criado') }}" aria-label="Token criado"
                    class="w-full rounded-md border border-amber-300 bg-white px-3 py-2 font-mono text-sm" onclick="this.select()">
                <button type="button" class="rounded-md border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100"
                    onclick="navigator.clipboard.writeText(document.getElementById('token-criado').value).then(() => this.textContent = 'Copiado')">Copiar</button>
            </div>
            <p class="mt-2 text-xs text-amber-800">Envie no cabeçalho <code class="font-mono">Authorization: Bearer &lt;token&gt;</code>.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('conta.tokens.store') }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <h2 class="text-lg font-semibold text-slate-900">Novo token</h2>
        <div class="mt-4 max-w-md">
            <x-campo nome="nome" rotulo="Nome" placeholder="ERP da loja" required maxlength="100" ajuda="Para lembrar onde o token é usado." />
        </div>
        <fieldset class="mt-4">
            <legend class="text-sm font-medium text-slate-700">Permissões</legend>
            <div class="mt-2 space-y-2">
                @foreach ($permissoes as $chave => $descricao)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="permissoes[]" value="{{ $chave }}" @checked(in_array($chave, old('permissoes', array_keys($permissoes)), true))
                            class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span><code class="font-mono text-slate-900">{{ $chave }}</code> <span class="text-slate-600">— {{ $descricao }}</span></span>
                    </label>
                @endforeach
            </div>
            @error('permissoes')
                <span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>
            @enderror
            @error('permissoes.*')
                <span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>
            @enderror
        </fieldset>
        <div class="mt-6">
            <x-botao>Criar token</x-botao>
        </div>
    </form>

    <h2 class="mt-10 text-lg font-semibold text-slate-900">Seus tokens</h2>
    @if ($tokens->isEmpty())
        <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">
            Nenhum token criado ainda.
        </div>
    @else
        <div class="relative mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">Permissões</th>
                        <th class="whitespace-nowrap px-4 py-3">Criado em</th>
                        <th class="whitespace-nowrap px-4 py-3">Último uso</th>
                        <th class="px-4 py-3"><span class="sr-only">Ações</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tokens as $token)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $token->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ implode(', ', $token->abilities ?? []) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $token->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $token->last_used_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('conta.tokens.destroy', $token->id) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Revogar o token '.$token->name.'? Integrações que o usam deixam de funcionar.') }})">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Revogar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
