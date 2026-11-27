@props(['nome', 'rotulo', 'valor' => null, 'tipo' => 'text', 'ajuda' => null, 'tooltip' => null])
@php
    $id = $attributes->get('id', 'campo-'.$nome);
    $valorAtual = old($nome, $valor);
    $moeda = $attributes->has('data-moeda');
    $descricoes = array_filter([
        $attributes->get('aria-describedby'),
        $tooltip ? $id.'-tooltip' : null,
        $moeda ? $nome.'-ajuda-moeda' : null,
    ]);
@endphp
<div class="min-w-0">
    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</label>
    <div @class(['campo-com-tooltip' => $tooltip])>
        <input
            id="{{ $id }}"
            type="{{ $tipo }}"
            name="{{ $nome }}"
            @if ($tipo !== 'password') value="{{ $valorAtual }}" @endif
            @error($nome) aria-invalid="true" @enderror
            @if ($descricoes) aria-describedby="{{ implode(' ', $descricoes) }}" @endif
            {{ $attributes->except(['id', 'aria-describedby'])->class('w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500') }}
        >
        @if ($tooltip)
            <x-tooltip-campo :id="$id.'-tooltip'">{{ $tooltip }}</x-tooltip-campo>
        @endif
    </div>
    @if ($moeda)
        <span id="{{ $nome }}-ajuda-moeda" class="mt-1 block text-xs text-slate-500" data-ajuda-moeda hidden>Digite 1000 para R$ 10,00.</span>
    @endif
    @if ($ajuda)
        <span class="mt-1 block text-xs text-slate-500">{{ $ajuda }}</span>
    @endif
    @error($nome)
        <span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>
    @enderror
</div>
