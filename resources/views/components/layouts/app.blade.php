@props(['titulo' => null])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-slate-900">Reforma<span class="text-emerald-600">360</span></a>
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <a href="{{ route('simulacoes.item.create') }}" class="text-slate-600 hover:text-slate-900">Simular item</a>
                @auth
                    @php($naoLidas = auth()->user()->unreadNotifications()->count())
                    <a href="{{ route('simulacoes.index') }}" class="text-slate-600 hover:text-slate-900">
                        Simulações
                        @if ($naoLidas > 0)
                            <span class="ml-1 inline-flex min-w-5 justify-center rounded-full bg-emerald-600 px-1.5 text-xs font-semibold text-white" aria-label="{{ $naoLidas }} {{ $naoLidas === 1 ? 'aviso novo' : 'avisos novos' }}">{{ $naoLidas }}</span>
                        @endif
                    </a>
                    <a href="{{ route('produtos.index') }}" class="text-slate-600 hover:text-slate-900">Produtos</a>
                    <a href="{{ route('importacoes.index') }}" class="text-slate-600 hover:text-slate-900">Importações</a>
                    <span class="hidden text-slate-400 sm:inline">{{ auth()->user()->empresa->razao_social }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-600 hover:text-slate-900">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-3 py-1.5 font-medium text-white hover:bg-emerald-700">Criar conta</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
        @endif
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-5xl px-4 pb-8 text-xs text-slate-500">
        Resultados são simulações feitas com a calculadora oficial da Receita Federal e não substituem a orientação de um contador.
    </footer>
</body>
</html>
