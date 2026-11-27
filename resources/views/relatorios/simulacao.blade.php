@php
    use App\Support\Formatador;

    $s = $relatorio->simulacao;
    $empresa = $s->empresa;
    $aliquotas = $s->aliquotasNominais();
    $moeda = fn (?string $v) => $v === null ? '—' : Formatador::moeda($v);
    $pct = fn (?string $v) => $v === null ? '—' : Formatador::percentual(str_contains($v, '.') ? (rtrim(rtrim($v, '0'), '.') ?: '0') : $v);
    $cnpj = $empresa->cnpj ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $empresa->cnpj) : null;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório da simulação #{{ $s->id }} — Reforma360</title>
    <style>
        @page { margin: 22mm 14mm 18mm 14mm; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #191919; }
        header.fixo { position: fixed; top: -14mm; left: 0; right: 0; font-size: 8pt; color: #666666; border-bottom: 1px solid #e5e5e5; padding-bottom: 2mm; }
        footer.fixo { position: fixed; bottom: -11mm; left: 0; right: 0; font-size: 7.5pt; color: #666666; border-top: 1px solid #e5e5e5; padding-top: 2mm; }
        h1 { font-size: 16pt; margin: 0 0 1mm 0; }
        h2 { font-size: 11.5pt; margin: 7mm 0 2mm 0; border-bottom: 1px solid #e5e5e5; padding-bottom: 1mm; }
        p { margin: 1mm 0; }
        .muted { color: #666666; }
        .aviso { border: 1px solid #f2a600; background: #fff8e6; padding: 3mm; margin: 4mm 0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 7.5pt; text-transform: uppercase; color: #666666; border-bottom: 1px solid #d4d4d4; padding: 1.5mm 1.5mm; }
        td { padding: 1.3mm 1.5mm; border-bottom: 1px solid #efefef; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .resumo td { border: 1px solid #e5e5e5; padding: 2.5mm; width: 25%; }
        .resumo .rotulo { font-size: 7.5pt; text-transform: uppercase; color: #666666; }
        .resumo .valor { font-size: 12pt; font-weight: bold; }
        .erro { color: #b42318; font-size: 7.5pt; }
        .fundamentacao { margin: 0 0 3mm 0; page-break-inside: avoid; }
        .fundamentacao .texto { font-size: 8pt; color: #333333; }
        tr { page-break-inside: avoid; }
        .bloco { page-break-inside: avoid; }
        .nowrap { white-space: nowrap; }
        footer.fixo { padding-right: 25mm; }
    </style>
</head>
<body>
    <header class="fixo">
        Reforma360 · Simulação #{{ $s->id }} · {{ $empresa->razao_social }}{{ $cnpj ? ' · CNPJ '.$cnpj : '' }}
    </header>
    <footer class="fixo">
        Simulação gerada em {{ $relatorio->geradoEm->format('d/m/Y H:i') }} com a calculadora oficial da Receita Federal. Não substitui a orientação de um contador.
    </footer>

    <h1>Relatório da simulação #{{ $s->id }}</h1>
    <p class="muted">
        {{ $empresa->razao_social }}{{ $cnpj ? ' · CNPJ '.$cnpj : '' }}<br>
        Fato gerador em {{ $s->data_fato_gerador->format('d/m/Y') }} · {{ $s->uf->value }}, município {{ $s->municipio }}
        @if ($s->importacao) · itens da {{ $s->importacao->tipo_documento?->label() ?? 'importação' }} nº {{ $s->importacao->numero_documento }} @endif
        <br>
        @if ($aliquotas)
            Alíquotas nominais informadas: CBS {{ $pct($aliquotas->cbs) }}, IBS estadual {{ $pct($aliquotas->ibsEstadual) }}, IBS municipal {{ $pct($aliquotas->ibsMunicipal) }}
        @else
            Alíquotas de referência da Receita para {{ $s->data_fato_gerador->year }}
        @endif
    </p>

    <div class="aviso">
        <strong>Simulação.</strong> Valores calculados pela calculadora oficial da Receita Federal (LC 214/2025)
        @if ($aliquotas)
            com alíquotas nominais informadas pelo usuário, que ainda não são as definitivas (resultado <strong>CALCULO_SIMULADO</strong>).
        @else
            com as alíquotas de referência do ano.
        @endif
        Este relatório é uma estimativa para apoiar a análise e não substitui a orientação de um contador nem a apuração oficial.
    </div>

    <h2>Resumo</h2>
    <table class="resumo">
        <tr>
            <td><div class="rotulo">Itens simulados</div><div class="valor">{{ $s->itens_processados - $s->itens_com_erro }} de {{ $s->total_itens }}</div></td>
            <td><div class="rotulo">Base de cálculo</div><div class="valor">{{ $moeda($s->total_base_calculo) }}</div></td>
            <td><div class="rotulo">CBS · IBS · IS</div><div class="valor" style="font-size: 10pt;">{{ $moeda($s->total_cbs) }} · {{ $moeda($s->total_ibs) }} · {{ $moeda($s->total_imposto_seletivo) }}</div></td>
            <td><div class="rotulo">Total de tributos</div><div class="valor">{{ $moeda($s->total_tributos) }}</div></td>
        </tr>
    </table>
    @if ($s->itens_com_erro > 0)
        <p class="muted">{{ $s->itens_com_erro }} {{ $s->itens_com_erro === 1 ? 'item não pôde ser simulado' : 'itens não puderam ser simulados' }} (motivo na lista de itens) e {{ $s->itens_com_erro === 1 ? 'fica' : 'ficam' }} fora dos totais.</p>
    @endif

    <h2>Por classificação tributária</h2>
    @if ($relatorio->porClassificacao === [])
        <p class="muted">Nenhum item calculado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>CST / cClassTrib</th>
                    <th>Tratamento</th>
                    <th class="num">Redução</th>
                    <th class="num">Itens</th>
                    <th class="num">Base</th>
                    <th class="num">CBS + IBS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($relatorio->porClassificacao as $grupo)
                    <tr>
                        <td class="nowrap">{{ $grupo['cst'] }} / {{ $grupo['c_class_trib'] }}</td>
                        <td>{{ $grupo['classificacao']?->tratamentoTributario ?? '—' }}@if ($grupo['classificacao'])<br><span class="muted">{{ $grupo['classificacao']->descricao }}</span>@endif</td>
                        <td class="num">{{ $pct($grupo['reducao']) }}</td>
                        <td class="num">{{ $grupo['itens'] }}</td>
                        <td class="num">{{ $moeda($grupo['base']) }}</td>
                        <td class="num">{{ $moeda($grupo['tributos']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($relatorio->comparacao)
        @php
            $c = $relatorio->comparacao;
            $diferenca = $c->diferenca();
        @endphp
        <h2>Simples Nacional × regime regular</h2>
        <p>
            Atividade {{ $c->atividade }} · alíquotas efetivas no Simples: CBS {{ $pct($c->aliquota_cbs_simples) }} e IBS {{ $pct($c->aliquota_ibs_simples) }}
            · {{ $c->itens_comparados }} itens comparados{{ $c->itens_nao_comparados > 0 ? ' ('.$c->itens_nao_comparados.' não comparados)' : '' }}.
        </p>
        <table class="resumo">
            <tr>
                <td><div class="rotulo">Simples Nacional</div><div class="valor">{{ $moeda($c->totalSimples()) }}</div></td>
                <td><div class="rotulo">Regime regular (débitos)</div><div class="valor">{{ $moeda($c->debitosRegular()) }}</div></td>
                <td><div class="rotulo">Crédito estimado das compras</div><div class="valor">{{ $c->compras !== null ? $moeda($c->credito_estimado) : 'não informado' }}</div></td>
                <td><div class="rotulo">Diferença</div><div class="valor">{{ $moeda(ltrim($diferenca, '-')) }}</div>
                    <div class="muted">{{ bccomp($diferenca, '0', 2) === 0 ? 'mesmo valor' : (bccomp($diferenca, '0', 2) > 0 ? 'a menos no Simples' : 'a menos no regime regular') }}</div></td>
            </tr>
        </table>
        <p class="muted">Compara CBS, IBS e IS dos itens calculados nos dois regimes. O crédito estimado (compras × alíquotas nominais do ano) incide sobre CBS e IBS. A escolha do regime envolve outros tributos (ICMS, ISS, IRPJ, CSLL, contribuição previdenciária).</p>
    @endif

    @if ($relatorio->linhaDoTempo)
        @php
            $plenas = $relatorio->linhaDoTempo->aliquotasPlenas();
        @endphp
        <div class="bloco">
        <h2>Linha do tempo 2026 → 2033</h2>
        <p class="muted">Cronograma da transição (EC 132/2023 e LC 214/2025) sobre alíquotas plenas <strong>estimadas</strong>: CBS {{ $pct($plenas->cbs) }}, IBS {{ $pct($plenas->ibsEstadual) }} + {{ $pct($plenas->ibsMunicipal) }}.</p>
        <table>
            <thead>
                <tr><th>Ano</th><th class="num">Alíquotas nominais</th><th class="num">CBS</th><th class="num">IBS</th><th class="num">IS</th><th class="num">Total</th><th class="num">Carga</th></tr>
            </thead>
            <tbody>
                @foreach ($relatorio->anos as $ano)
                    <tr>
                        <td>{{ $ano->ano }}@if ($ano->itensComErro > 0) <span class="erro">({{ $ano->itensComErro }} com erro)</span>@endif</td>
                        <td class="num">{{ $ano->aliquotas ? 'CBS '.$pct($ano->aliquotas->cbs).' · IBS '.$pct((string) $ano->aliquotaIbsTotal()) : 'referência da Receita' }}</td>
                        <td class="num">{{ $moeda($ano->cbs) }}</td>
                        <td class="num">{{ $moeda($ano->ibs) }}</td>
                        <td class="num">{{ $moeda($ano->impostoSeletivo) }}</td>
                        <td class="num">{{ $moeda($ano->total) }}</td>
                        <td class="num">{{ $pct($ano->carga) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif

    <h2>Itens{{ $relatorio->itensTruncados ? ' (os '.$relatorio->limiteItens.' de maior carga)' : '' }}</h2>
    @if ($relatorio->itensTruncados)
        <p class="muted">A simulação tem {{ $s->total_itens }} itens; o detalhamento completo está na tela da simulação.</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>NCM / NBS</th>
                <th>CST / cClassTrib</th>
                <th class="num">Base</th>
                <th class="num">Redução</th>
                <th class="num">CBS</th>
                <th class="num">IBS</th>
                <th class="num">IS</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($relatorio->itens as $item)
                <tr>
                    <td>
                        {{ $item->descricao }}
                        @if ($item->temErro())
                            @foreach ($item->erros ?? [] as $erro)
                                <br><span class="erro">{{ $erro['codigo'] ? $erro['codigo'].': ' : '' }}{{ $erro['mensagem'] }}</span>
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $item->ncm ?? $item->nbs }}</td>
                    <td class="nowrap">{{ $item->cst }} / {{ $item->c_class_trib }}</td>
                    <td class="num">{{ $moeda($item->base_calculo) }}</td>
                    <td class="num">{{ $item->percentual_reducao !== null && bccomp($item->percentual_reducao, '0', 4) > 0 ? $pct($item->percentual_reducao) : '—' }}</td>
                    <td class="num">{{ $moeda($item->valor_cbs) }}</td>
                    <td class="num">{{ $moeda($item->valor_ibs) }}</td>
                    <td class="num">{{ $moeda($item->valor_imposto_seletivo) }}</td>
                    <td class="num">{{ $moeda($item->total_tributos) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Fundamentação legal</h2>
    @if ($relatorio->fundamentacaoIndisponivel)
        <p class="muted">Os dados abertos da calculadora estavam indisponíveis ao gerar o relatório; a fundamentação legal não foi incluída. Gere o relatório de novo em instantes.</p>
    @else
        @forelse (collect($relatorio->porClassificacao)->filter(fn ($g) => $g['fundamentacao'] !== null) as $grupo)
            <div class="fundamentacao">
                <p><strong>cClassTrib {{ $grupo['c_class_trib'] }}</strong>&nbsp;— {{ $grupo['fundamentacao']->textoCurto }} · {{ $grupo['fundamentacao']->situacaoTributaria }}</p>
                <p class="texto">{!! nl2br(e($grupo['fundamentacao']->texto)) !!}</p>
            </div>
        @empty
            <p class="muted">Sem fundamentação disponível para as classificações dos itens calculados.</p>
        @endforelse
    @endif
</body>
</html>
