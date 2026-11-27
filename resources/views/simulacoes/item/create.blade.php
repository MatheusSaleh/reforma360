@php
    use App\Support\Formatador;

    $valor = fn (string $campo, ?string $padrao = null) => old($campo, request()->query($campo, $padrao));
    $sugeridas = config('simulacao.aliquotas_nominais_sugeridas');
    $anoAliquotas = config('simulacao.ano_inicio_aliquotas_nominais');
@endphp

<x-layouts.app titulo="Simular um item">
    <h1 class="text-2xl font-semibold text-slate-900">Simular um item</h1>
    <p class="mt-1 text-sm text-slate-600">Informe o produto ou serviço e simule a CBS, o IBS e, quando aplicável, o Imposto Seletivo com a calculadora da Receita Federal.</p>

    @error('simulacao')
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
    @enderror

    <form method="GET" action="{{ route('simulacoes.item.resultado') }}" class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm" novalidate>
        <fieldset class="grid gap-4 sm:grid-cols-2">
            <legend class="mb-2 text-sm font-semibold text-slate-900">Item</legend>

            <x-campo-nomenclatura tipo="ncm" rotulo="NCM (mercadoria)" :valor="$valor('ncm')" placeholder="Código ou descrição" ajuda="Preencha o NCM ou o NBS." />
            <x-campo-nomenclatura tipo="nbs" rotulo="NBS (serviço)" :valor="$valor('nbs')" placeholder="Código ou descrição" />
            <x-campos-classificacao :cst="$valor('cst')" :c-class-trib="$valor('c_class_trib')" />

            <x-campo nome="valor" rotulo="Valor (base de cálculo sem IS)" :valor="$valor('valor')"
                inputmode="decimal" placeholder="1.200,00" data-moeda
                tooltip="Valor em reais usado como base da simulação. Informe a base do item antes de acrescentar o Imposto Seletivo." />
        </fieldset>

        @include('simulacoes._parametros', ['ufs' => $ufs])

        <x-campos-imposto-seletivo :valor="$valor" quantidade="Quantidade total na unidade do IS" />

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
                                <td class="py-2 text-right">
                                    @if ($simulacao->impostoSeletivo->aliquota !== null)
                                        {{ Formatador::percentual($simulacao->impostoSeletivo->aliquota) }}
                                    @endif
                                    @if ($simulacao->impostoSeletivo->aliquotaAdRem !== null)
                                        <span class="block">R$ {{ str_replace('.', ',', $simulacao->impostoSeletivo->aliquotaAdRem) }} / {{ $simulacao->impostoSeletivo->unidade }}</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">—</td>
                                <td class="py-2 text-right">—</td>
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
