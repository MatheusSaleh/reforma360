@props(['tipo', 'rotulo', 'valor' => null, 'ajuda' => null])
@php
    $tipo = $tipo instanceof \App\Enums\TipoNomenclatura ? $tipo : \App\Enums\TipoNomenclatura::from($tipo);
    $nome = $tipo->value;
    $id = 'campo-'.$nome;
    $tooltip = match ($tipo) {
        \App\Enums\TipoNomenclatura::Ncm => 'Nomenclatura Comum do Mercosul. É o código de 8 dígitos que identifica uma mercadoria, como cerveja de malte.',
        \App\Enums\TipoNomenclatura::Nbs => 'Nomenclatura Brasileira de Serviços. É o código de 9 dígitos que identifica o tipo de serviço prestado.',
    };
@endphp
<div class="relative"
    data-autocomplete-nomenclatura
    data-tipo="{{ $tipo->value }}"
    data-digitos="{{ $tipo->digitos() }}"
    data-url-busca="{{ route('dados-abertos.nomenclaturas.busca', $tipo) }}"
    data-url-detalhe="{{ route('dados-abertos.nomenclaturas.detalhe', [$tipo, '__CODIGO__']) }}">
    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</label>
    <div class="campo-dropdown-controle campo-com-tooltip">
        <input
            id="{{ $id }}"
            type="text"
            name="{{ $nome }}"
            value="{{ old($nome, $valor) }}"
            inputmode="search"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="{{ $id }}-opcoes"
            aria-describedby="{{ $id }}-tooltip {{ $id }}-detalhe"
            @error($nome) aria-invalid="true" @enderror
            {{ $attributes->class('campo-dropdown') }}
        >
        <ul id="{{ $id }}-opcoes" class="lista-opcoes" role="listbox" aria-label="Sugestões de {{ $tipo->label() }}" hidden></ul>
        <x-tooltip-campo :id="$id.'-tooltip'">{{ $tooltip }}</x-tooltip-campo>
    </div>
    <p id="{{ $id }}-detalhe" class="mt-1 text-xs text-slate-600" aria-live="polite" data-detalhe></p>
    @if ($ajuda)
        <span class="mt-1 block text-xs text-slate-500">{{ $ajuda }}</span>
    @endif
    @error($nome)
        <span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>
    @enderror
</div>
