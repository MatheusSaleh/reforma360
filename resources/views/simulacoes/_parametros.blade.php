@php
    $valor = fn (string $campo, ?string $padrao = null) => old($campo, request()->query($campo, $padrao));
    $sugeridas = config('simulacao.aliquotas_nominais_sugeridas');
    $anoAliquotas = config('simulacao.ano_inicio_aliquotas_nominais');
    $input = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
@endphp
{{-- Parâmetros da operação (UF, município, data e alíquotas): simulador de um item e simulação em lote. --}}
    <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-2 text-sm font-semibold text-slate-900">Operação</legend>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">UF</span>
            <select name="uf" class="{{ $input }}" data-uf>
                <option value="">Selecione</option>
                @foreach ($ufs as $uf)
                    <option value="{{ $uf->value }}" @selected($valor('uf') === $uf->value)>{{ $uf->value }}</option>
                @endforeach
            </select>
            @error('uf')<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Município</span>
            <select name="municipio" class="{{ $input }}" data-municipio data-selecionado="{{ $valor('municipio') }}" data-url="{{ route('dados-abertos.municipios', ['uf' => '__UF__']) }}">
                <option value="">Selecione a UF primeiro</option>
            </select>
            @error('municipio')<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Data do fato gerador</span>
            <input type="date" name="data" value="{{ $valor('data', now()->toDateString()) }}" min="{{ config('simulacao.data_minima') }}" max="{{ config('simulacao.data_maxima') }}" class="{{ $input }}">
            @error('data')<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
        </label>
    </fieldset>

    <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-1 text-sm font-semibold text-slate-900">Alíquotas nominais (%)</legend>
        <p class="text-xs text-slate-500 sm:col-span-3">
            Usadas só para datas a partir de {{ $anoAliquotas }}, quando a calculadora exige que você informe as alíquotas.
            Os valores sugeridos são estimativas editáveis, não alíquotas definidas em lei. Antes de {{ $anoAliquotas }}, valem as alíquotas de referência da Receita.
        </p>

        @foreach ([
            ['aliquota_cbs', 'CBS', 'cbs'],
            ['aliquota_ibs_estadual', 'IBS estadual', 'ibs_estadual'],
            ['aliquota_ibs_municipal', 'IBS municipal', 'ibs_municipal'],
        ] as [$campo, $rotulo, $chave])
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">{{ $rotulo }}</span>
                <input type="text" inputmode="decimal" name="{{ $campo }}" value="{{ $valor($campo, $sugeridas[$chave]) }}" class="{{ $input }}">
                @error($campo)<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
            </label>
        @endforeach
    </fieldset>
