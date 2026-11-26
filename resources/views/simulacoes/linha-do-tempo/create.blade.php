@php
    $valor = fn (string $campo, ?string $atual, string $sugerida) => old($campo, $atual ?? $sugerida);
@endphp
<x-layouts.app :titulo="'Linha do tempo · Simulação #'.$simulacao->id">
    <a href="{{ route('simulacoes.show', $simulacao) }}" class="text-sm text-slate-600 hover:text-slate-900">← Simulação #{{ $simulacao->id }}</a>
    <h1 class="mt-2 text-2xl font-semibold text-slate-900">Linha do tempo 2026 → 2033</h1>
    <p class="mt-1 text-sm text-slate-600">
        Os {{ $simulacao->total_itens }} itens da simulação #{{ $simulacao->id }} serão simulados em cada ano da transição,
        no mesmo local ({{ $simulacao->uf->value }}, município {{ $simulacao->municipio }}) e no mesmo dia e mês do fato gerador.
    </p>

    @error('linha_do_tempo')
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
    @enderror

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('simulacoes.linha-do-tempo.store', $simulacao) }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2" novalidate>
            @csrf
            <fieldset class="grid gap-4 sm:grid-cols-3">
                <legend class="mb-1 text-sm font-semibold text-slate-900">Alíquotas plenas estimadas (%)</legend>
                <p class="text-xs text-slate-500 sm:col-span-3">
                    As alíquotas plenas de CBS e IBS ainda não foram fixadas. Os valores sugeridos são estimativas
                    (CBS 8,8% + IBS 17,7%, cerca de 26,5% no total, conforme estimativa do Ministério da Fazenda) e podem ser editados.
                    A divisão do IBS entre estadual e municipal não altera o total.
                </p>
                <x-campo nome="aliquota_plena_cbs" rotulo="CBS" :valor="$valor('aliquota_plena_cbs', $plenas?->cbs, $sugeridas['cbs'])" inputmode="decimal" />
                <x-campo nome="aliquota_plena_ibs_estadual" rotulo="IBS estadual" :valor="$valor('aliquota_plena_ibs_estadual', $plenas?->ibsEstadual, $sugeridas['ibs_estadual'])" inputmode="decimal" />
                <x-campo nome="aliquota_plena_ibs_municipal" rotulo="IBS municipal" :valor="$valor('aliquota_plena_ibs_municipal', $plenas?->ibsMunicipal, $sugeridas['ibs_municipal'])" inputmode="decimal" />
            </fieldset>
            <x-botao>{{ $plenas ? 'Refazer a linha do tempo' : 'Gerar linha do tempo' }}</x-botao>
            @if ($plenas)
                <p class="text-xs text-slate-500">Refazer substitui a linha do tempo atual.</p>
            @endif
        </form>

        <aside class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-700 shadow-sm">
            <h2 class="font-semibold text-slate-900">Cronograma da transição</h2>
            <p class="mt-1 text-xs text-slate-500">EC 132/2023 (ADCT, arts. 125 a 128) e LC 214/2025</p>
            <dl class="mt-3 space-y-2">
                <div><dt class="inline font-semibold">2026</dt> <dd class="inline text-slate-600">— teste: alíquotas de referência da Receita (CBS 0,9%, IBS 0,1%)</dd></div>
                <div><dt class="inline font-semibold">2027–2028</dt> <dd class="inline text-slate-600">— CBS plena menos 0,1 p.p.; IBS 0,1%</dd></div>
                <div><dt class="inline font-semibold">2029–2032</dt> <dd class="inline text-slate-600">— CBS plena; IBS a 10%, 20%, 30% e 40% da plena</dd></div>
                <div><dt class="inline font-semibold">2033</dt> <dd class="inline text-slate-600">— CBS e IBS plenas</dd></div>
            </dl>
            <p class="mt-3 text-xs text-slate-500">O ICMS, o ISS, o PIS e a Cofins que deixam de existir na transição não entram no cálculo.</p>
        </aside>
    </div>
</x-layouts.app>
