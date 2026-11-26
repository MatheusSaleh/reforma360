@php
    use App\Support\Formatador;

    $valor = fn (string $campo, ?string $padrao = null) => old($campo, request()->query($campo, $padrao));
    $sugeridas = config('simulacao.aliquotas_nominais_sugeridas');
    $anoAliquotas = config('simulacao.ano_inicio_aliquotas_nominais');
    $input = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
@endphp

<x-layouts.app titulo="Simular um item">
    <h1 class="text-2xl font-semibold text-slate-900">Simular um item</h1>
    <p class="mt-1 text-sm text-slate-600">Informe o produto ou serviço e veja a CBS e o IBS calculados pela calculadora da Receita Federal.</p>

    @error('simulacao')
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
    @enderror

    <form method="GET" action="{{ route('simulacoes.item.resultado') }}" class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm" novalidate>
        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-2 text-sm font-semibold text-slate-900">Item</legend>

            <x-campo-nomenclatura tipo="ncm" rotulo="NCM (mercadoria)" :valor="$valor('ncm')" placeholder="Código ou descrição" ajuda="Preencha o NCM ou o NBS." />
            <x-campo-nomenclatura tipo="nbs" rotulo="NBS (serviço)" :valor="$valor('nbs')" placeholder="Código ou descrição" />
            <x-campos-classificacao :cst="$valor('cst')" :c-class-trib="$valor('c_class_trib')" />

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Valor (base de cálculo)</span>
                <input type="text" inputmode="decimal" name="valor" value="{{ $valor('valor') }}" placeholder="1.200,00" class="{{ $input }}">
                @error('valor')<span class="mt-1 block text-sm text-red-600" role="alert">{{ $message }}</span>@enderror
            </label>
        </fieldset>

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

        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Simular
        </button>
    </form>

    @if ($simulacao)
        @php($ibsCbs = $simulacao->ibsCbs)
        <section class="mt-8 space-y-6" aria-labelledby="resultado">
            <div class="rounded-md border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
                <strong>Simulação.</strong>
                @if ($simulacao->usouAliquotasInformadas)
                    A calculadora marcou este resultado como <code>CALCULO_SIMULADO</code>: foram usadas as alíquotas nominais informadas no formulário, que ainda não são as definitivas.
                @else
                    Calculado com as alíquotas de referência da Receita para {{ $simulacao->entrada->data->year }}.
                @endif
                O resultado não substitui a orientação de um contador.
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 id="resultado" class="text-lg font-semibold text-slate-900">Resultado</h2>
                @if ($simulacao->classificacao)
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $simulacao->classificacao->codigo }} · {{ $simulacao->classificacao->descricao }}
                        — <strong>{{ $simulacao->classificacao->tratamentoTributario }}</strong>
                    </p>
                @endif

                <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-md bg-slate-50 p-4">
                        <dt class="text-xs uppercase text-slate-500">Base de cálculo</dt>
                        <dd class="mt-1 text-xl font-semibold">{{ Formatador::moeda($ibsCbs->baseCalculo) }}</dd>
                    </div>
                    <div class="rounded-md bg-slate-50 p-4">
                        <dt class="text-xs uppercase text-slate-500">CBS + IBS{{ $simulacao->impostoSeletivo ? ' + IS' : '' }}</dt>
                        <dd class="mt-1 text-xl font-semibold text-emerald-700">{{ Formatador::moeda($simulacao->totalTributos) }}</dd>
                    </div>
                    <div class="rounded-md bg-slate-50 p-4">
                        <dt class="text-xs uppercase text-slate-500">Data do fato gerador</dt>
                        <dd class="mt-1 text-xl font-semibold">{{ $simulacao->entrada->data->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                <table class="mt-6 w-full text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-2">Tributo</th>
                            <th class="py-2 text-right">Alíquota nominal</th>
                            <th class="py-2 text-right">Redução</th>
                            <th class="py-2 text-right">Alíquota efetiva</th>
                            <th class="py-2 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach (['CBS' => $ibsCbs->cbs, 'IBS estadual' => $ibsCbs->ibsEstadual, 'IBS municipal' => $ibsCbs->ibsMunicipal] as $nome => $parcela)
                            <tr>
                                <td class="py-2 font-medium">{{ $nome }}</td>
                                <td class="py-2 text-right">{{ Formatador::percentual($parcela->aliquota) }}</td>
                                <td class="py-2 text-right">{{ $parcela->percentualReducao ? Formatador::percentual($parcela->percentualReducao) : '—' }}</td>
                                <td class="py-2 text-right">{{ Formatador::percentual($parcela->aliquotaEfetiva ?? $parcela->aliquota) }}</td>
                                <td class="py-2 text-right">{{ Formatador::moeda($parcela->valor) }}</td>
                            </tr>
                        @endforeach
                        @if ($simulacao->impostoSeletivo)
                            <tr>
                                <td class="py-2 font-medium">Imposto Seletivo</td>
                                <td class="py-2 text-right">{{ Formatador::percentual($simulacao->impostoSeletivo->aliquota) }}</td>
                                <td class="py-2 text-right">—</td>
                                <td class="py-2 text-right">{{ Formatador::percentual($simulacao->impostoSeletivo->aliquota) }}</td>
                                <td class="py-2 text-right">{{ Formatador::moeda($simulacao->impostoSeletivo->valor) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Memória de cálculo</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    @foreach (['CBS' => $ibsCbs->memoriaCbs, 'IBS estadual' => $ibsCbs->memoriaIbsEstadual, 'IBS municipal' => $ibsCbs->memoriaIbsMunicipal, 'Imposto Seletivo' => $simulacao->impostoSeletivo?->memoriaCalculo] as $nome => $memoria)
                        @if ($memoria)
                            <li><strong>{{ $nome }}:</strong> {{ $memoria }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>

            @if ($simulacao->fundamentacao)
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Fundamentação legal</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $simulacao->fundamentacao->textoCurto }} · {{ $simulacao->fundamentacao->situacaoTributaria }}</p>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $simulacao->fundamentacao->texto }}</p>
                    <details class="mt-3 text-sm text-slate-600">
                        <summary class="cursor-pointer font-medium">Referência normativa</summary>
                        <p class="mt-2 whitespace-pre-line">{{ $simulacao->fundamentacao->referenciaNormativa }}</p>
                    </details>
                </div>
            @endif

            @if ($simulacao->versaoBaseDados)
                <p class="text-xs text-slate-500">Base de dados da calculadora: {{ $simulacao->versaoBaseDados }}</p>
            @endif
        </section>
    @endif
</x-layouts.app>
