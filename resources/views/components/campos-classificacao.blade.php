@props(['cst' => null, 'cClassTrib' => null])
{{-- CST e cClassTrib com sugestões dos dados abertos (datalist): funciona como texto sem JS. --}}
<div data-classificacoes data-url="{{ route('dados-abertos.situacoes-tributarias') }}" class="contents">
    <div>
        <x-campo nome="cst" rotulo="CST" :valor="$cst" inputmode="numeric" list="opcoes-cst" autocomplete="off" ajuda="3 dígitos. Comece a digitar para ver as opções." data-cst />
        <datalist id="opcoes-cst"></datalist>
    </div>
    <div>
        <x-campo nome="c_class_trib" rotulo="cClassTrib" :valor="$cClassTrib" inputmode="numeric" list="opcoes-c-class-trib" autocomplete="off" ajuda="6 dígitos. As opções seguem o CST escolhido." data-c-class-trib />
        <datalist id="opcoes-c-class-trib"></datalist>
        <p class="mt-1 text-xs text-slate-600" aria-live="polite" data-c-class-trib-descricao></p>
    </div>
</div>
