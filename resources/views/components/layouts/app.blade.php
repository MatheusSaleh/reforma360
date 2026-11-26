@props(['titulo' => null])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo ? $titulo.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-slate-900">Reforma<span class="text-emerald-600">360</span></a>
            <span class="text-sm text-slate-500">Simulador CBS/IBS · LC 214/2025</span>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-5xl px-4 pb-8 text-xs text-slate-500">
        Resultados são simulações feitas com a calculadora oficial da Receita Federal e não substituem a orientação de um contador.
    </footer>
</body>
</html>
