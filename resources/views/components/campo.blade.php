@props(['nome', 'rotulo', 'valor' => null, 'tipo' => 'text', 'ajuda' => null])
@php($valorAtual = old($nome, $valor))
<label class="block">
    <span class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</span>
    <input
        type="{{ $tipo }}"
        name="{{ $nome }}"
        @if ($tipo !== 'password') value="{{ $valorAtual }}" @endif
        @error($nome) aria-invalid="true" @enderror
        {{ $attributes->merge(['class' => 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500']) }}
    >
    @if ($ajuda)
        <span class="mt-1 block text-xs text-slate-500">{{ $ajuda }}</span>
    @endif
    @error($nome)
        <span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>
    @enderror
</label>
