@php
    use App\Support\Formatador;
@endphp
<x-layouts.app :titulo="'Simples × regime regular · Simulação #'.$simulacao->id">
    <a href="{{ route('simulacoes.show', $simulacao) }}" class="text-sm text-slate-600 hover:text-slate-900">← Simulação #{{ $simulacao->id }}</a>
    <h1 class="mt-2 text-2xl font-semibold text-slate-900">Simples Nacional × regime regular</h1>
    <p class="mt-1 text-sm text-slate-600">
        Compare o IBS e a CBS dos itens da simulação #{{ $simulacao->id }} ({{ $simulacao->data_fato_gerador->format('d/m/Y') }})
        no regime regular com o que seria recolhido no Simples Nacional.
    </p>

    @foreach (['comparacao', 'simulacao'] as $chave)
        @error($chave)
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror
    @endforeach

    @if ($impedimento)
        <div class="mt-6 rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">{{ $impedimento }}</div>
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <form method="POST" action="{{ route('simulacoes.comparacao.store', $simulacao) }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2" novalidate>
                @csrf
                <x-seletor-pesquisavel id="campo-atividade" rotulo="Atividade no Simples Nacional" name="atividade">
                    <option value="">Selecione</option>
                    @foreach ($atividades as $atividade)
                        <option value="{{ $atividade->codigo }}" @selected(old('atividade', $atual?->atividade ?? '01') === $atividade->codigo)>
                            {{ $atividade->codigo }} · {{ $atividade->descricao }} (Anexo {{ $atividade->anexo }})
                        </option>
                    @endforeach
                </x-seletor-pesquisavel>

                <fieldset class="grid gap-4 sm:grid-cols-2">
                    <legend class="mb-1 text-sm font-semibold text-slate-900">Alíquotas efetivas no Simples (%)</legend>
                    <p class="text-xs text-slate-500 sm:col-span-2">
                        As alíquotas efetivas da CBS e do IBS dentro do Simples dependem da sua receita bruta dos últimos 12 meses e do anexo.
                        Use as do PGDAS ou peça ao seu contador. O app não as calcula.
                    </p>
                    <x-campo nome="aliquota_cbs_simples" rotulo="CBS no Simples" :valor="$atual?->aliquota_cbs_simples" inputmode="decimal" placeholder="Ex.: 2,75" />
                    <x-campo nome="aliquota_ibs_simples" rotulo="IBS no Simples" :valor="$atual?->aliquota_ibs_simples" inputmode="decimal" placeholder="Ex.: 0,50" />
                </fieldset>

                <x-campo nome="compras" rotulo="Compras com crédito no período (opcional, R$)" :valor="$atual?->compras" inputmode="decimal" data-moeda placeholder="Ex.: 2.000,00"
                    ajuda="No regime regular, a empresa toma crédito do IBS e da CBS das compras. Informe o valor das compras do mesmo período para estimar esse crédito." />

                <x-botao>{{ $atual ? 'Refazer a comparação' : 'Comparar' }}</x-botao>
            </form>

            <aside class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-700 shadow-sm">
                <h2 class="font-semibold text-slate-900">Como a comparação funciona</h2>
                <ul class="mt-2 list-disc space-y-1.5 pl-4 text-slate-600">
                    <li>Regime regular: os valores já calculados nesta simulação{{ $simulacao->aliquotasNominais() ? ' (alíquotas '.Formatador::percentual($simulacao->aliquotasNominais()->cbs).' + '.Formatador::percentual($simulacao->aliquotasNominais()->ibsEstadual).' + '.Formatador::percentual($simulacao->aliquotasNominais()->ibsMunicipal).')' : '' }}, menos o crédito estimado das compras.</li>
                    <li>Simples Nacional: calculado pela calculadora oficial do Simples com as alíquotas efetivas informadas.</li>
                    <li>Só mercadorias (NF-e/NFC-e): a calculadora do Simples ainda não calcula serviços.</li>
                    <li>Não considera ICMS, ISS, IRPJ, CSLL e contribuição previdenciária, que também mudam com a opção.</li>
                </ul>
            </aside>
        </div>
    @endif
</x-layouts.app>
