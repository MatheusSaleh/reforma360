@props(['id', 'rotulo', 'tooltip' => null])
@php
    $nome = $attributes->get('name');
    $descricoes = array_filter([$tooltip ? $id.'-tooltip' : null, $errors->has($nome) ? $id.'-erro' : null]);
@endphp

<div class="min-w-0" data-seletor-pesquisavel>
    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</label>
    <div @class(['campo-dropdown-controle', 'campo-com-tooltip' => $tooltip])>
        <select id="{{ $id }}" {{ $attributes->class('campo-dropdown') }}
            @error($nome) aria-invalid="true" @enderror
            @if ($descricoes) aria-describedby="{{ implode(' ', $descricoes) }}" @endif>
            {{ $slot }}
        </select>
        <input id="{{ $id }}-pesquisa" class="campo-dropdown" type="text" autocomplete="off"
            role="combobox" aria-autocomplete="list" aria-controls="{{ $id }}-opcoes" aria-expanded="false"
            @error($nome) aria-invalid="true" @enderror
            @if ($descricoes) aria-describedby="{{ implode(' ', $descricoes) }}" @endif
            placeholder="Pesquisar..." hidden>
        <button type="button" class="campo-dropdown-abrir" aria-label="Mostrar opções de {{ $rotulo }}" tabindex="-1" hidden></button>
        <ul id="{{ $id }}-opcoes" class="lista-opcoes" role="listbox" aria-label="Opções de {{ $rotulo }}" hidden></ul>
        @if ($tooltip)
            <x-tooltip-campo :id="$id.'-tooltip'">{{ $tooltip }}</x-tooltip-campo>
        @endif
    </div>
    @error($nome)
        <p id="{{ $id }}-erro" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
</div>
