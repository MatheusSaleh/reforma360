@php
    $valor = fn (string $campo, ?string $padrao = null) => old($campo, request()->query($campo, $padrao));
    $sugeridas = config('simulacao.aliquotas_nominais_sugeridas');
    $anoAliquotas = config('simulacao.ano_inicio_aliquotas_nominais');
    $estados = collect($ufs)->sortBy(fn (\App\Enums\Uf $uf) => \Illuminate\Support\Str::ascii($uf->label()));
@endphp
{{-- Parâmetros da operação (UF, município, data e alíquotas): simulador de um item e simulação em lote. --}}
    <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-2 text-sm font-semibold text-slate-900">Operação</legend>

        <x-seletor-pesquisavel id="campo-uf" rotulo="UF" name="uf" data-uf
            tooltip="Unidade Federativa. Selecione o estado ou o Distrito Federal correspondente ao município da operação.">
            <option value="">Selecione</option>
            @foreach ($estados as $uf)
                <option value="{{ $uf->value }}" @selected($valor('uf') === $uf->value)>{{ $uf->label() }} · {{ $uf->value }}</option>
            @endforeach
        </x-seletor-pesquisavel>

        <x-seletor-pesquisavel id="campo-municipio" rotulo="Município" name="municipio" data-municipio
            tooltip="Município associado ao local da operação na simulação. A lista mostra os municípios da UF selecionada."
            :data-selecionado="$valor('municipio')" :data-url="route('dados-abertos.municipios', ['uf' => '__UF__'])">
            <option value="">Selecione a UF primeiro</option>
        </x-seletor-pesquisavel>

        <x-campo nome="data" rotulo="Data do fato gerador" tipo="date" :valor="$valor('data', now()->toDateString())"
            :min="config('simulacao.data_minima')" :max="config('simulacao.data_maxima')"
            tooltip="Data em que ocorre o fato que dá origem ao tributo. Ela define as regras de transição usadas na simulação." />
    </fieldset>

    <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-1 text-sm font-semibold text-slate-900">Alíquotas nominais (%)</legend>
        <p class="text-xs text-slate-500 sm:col-span-3">
            Usadas só para datas a partir de {{ $anoAliquotas }}, quando a calculadora exige que você informe as alíquotas.
            Os valores sugeridos são estimativas editáveis, não alíquotas definidas em lei. Antes de {{ $anoAliquotas }}, valem as alíquotas de referência da Receita.
        </p>

        @foreach ([
            ['aliquota_cbs', 'CBS', 'cbs', 'Percentual federal da Contribuição sobre Bens e Serviços, antes de eventuais reduções.'],
            ['aliquota_ibs_estadual', 'IBS estadual', 'ibs_estadual', 'Percentual estadual do Imposto sobre Bens e Serviços, antes de eventuais reduções.'],
            ['aliquota_ibs_municipal', 'IBS municipal', 'ibs_municipal', 'Percentual municipal do Imposto sobre Bens e Serviços, antes de eventuais reduções.'],
        ] as [$campo, $rotulo, $chave, $tooltip])
            <x-campo :nome="$campo" :rotulo="$rotulo" :valor="$valor($campo, $sugeridas[$chave])" :tooltip="$tooltip" inputmode="decimal" />
        @endforeach
    </fieldset>
