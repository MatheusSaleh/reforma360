@props(['cst' => null, 'cClassTrib' => null])

<div data-classificacoes data-url="{{ route('dados-abertos.situacoes-tributarias') }}"
    data-explicacoes-cst='@json(__('classificacoes.explicacoes_cst'))' class="contents">
    @foreach ([
        ['cst', 'CST', $cst, 3, '3 dígitos. Digite ou abra a lista para ver as opções.', 'Código de Situação Tributária. Indica como o IBS e a CBS incidem nesta operação, por exemplo com tributação integral ou redução de alíquota.'],
        ['c_class_trib', 'cClassTrib', $cClassTrib, 6, '6 dígitos. As opções seguem o CST escolhido.', 'Código de Classificação Tributária. Identifica a regra específica de IBS e CBS dentro do CST selecionado.'],
    ] as [$nome, $rotulo, $valor, $digitos, $ajuda, $tooltip])
        <div class="min-w-0">
            <label for="campo-{{ $nome }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</label>
            <div class="campo-dropdown-controle campo-com-tooltip">
                <input id="campo-{{ $nome }}" type="text" name="{{ $nome }}" value="{{ old($nome, $valor) }}"
                    class="campo-dropdown" inputmode="numeric" data-digitos="{{ $digitos }}" autocomplete="off" aria-describedby="{{ $nome }}-tooltip {{ $nome }}-ajuda"
                    @error($nome) aria-invalid="true" @enderror>
                <button type="button" class="campo-dropdown-abrir" aria-label="Mostrar opções de {{ $rotulo }}" tabindex="-1" hidden></button>
                <ul id="{{ $nome }}-opcoes" class="lista-opcoes" role="listbox" aria-label="Opções de {{ $rotulo }}" hidden></ul>
                <x-tooltip-campo :id="$nome.'-tooltip'">{{ $tooltip }}</x-tooltip-campo>
            </div>
            <p id="{{ $nome }}-ajuda" class="mt-1 text-xs text-slate-500">{{ $ajuda }}</p>
            @error($nome)
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
            @if ($nome === 'c_class_trib')
                <p class="mt-1 text-xs text-slate-600" aria-live="polite" data-c-class-trib-descricao></p>
            @endif
        </div>
    @endforeach
</div>
