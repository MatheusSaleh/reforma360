@php
    $rotulos = ['descricao' => 'Descrição', 'ncm' => 'NCM', 'nbs' => 'NBS', 'cst' => 'CST', 'c_class_trib' => 'cClassTrib', 'preco' => 'Preço'];
@endphp
<x-layouts.app titulo="Importação">
    @if ($importacao->status->emAndamento())
        @push('head')<meta http-equiv="refresh" content="3">@endpush
    @endif

    <a href="{{ route('importacoes.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Importações</a>
    <div class="mt-2 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $importacao->nome_arquivo }}</h1>
        @include('importacoes._status', ['status' => $importacao->status])
    </div>

    @if ($importacao->ehDocumentoFiscal())
        <p class="mt-1 text-sm text-slate-600">
            {{ $importacao->tipo_documento->label() }} nº {{ $importacao->numero_documento }}
            @if ($importacao->data_documento) · emitida em {{ $importacao->data_documento->format('d/m/Y') }} @endif
            @if ($importacao->uf) · {{ $importacao->uf->value }}, município {{ $importacao->municipio }} @endif
        </p>
    @endif

    @if ($importacao->status->emAndamento())
        <p class="mt-2 text-sm text-slate-600" role="status">Processando a planilha… esta página se atualiza sozinha.</p>
    @endif

    @if ($importacao->mensagem_erro)
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $importacao->mensagem_erro }}</div>
    @endif

    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Linhas lidas</dt>
            <dd class="mt-1 text-2xl font-semibold">{{ $importacao->total_linhas }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Produtos importados</dt>
            <dd class="mt-1 text-2xl font-semibold text-emerald-700">{{ $importacao->linhas_importadas }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <dt class="text-xs uppercase text-slate-500">Linhas com erro</dt>
            <dd class="mt-1 text-2xl font-semibold {{ $importacao->linhas_com_erro > 0 ? 'text-red-700' : '' }}">{{ $importacao->linhas_com_erro }}</dd>
        </div>
    </dl>

    @if ($importacao->status === \App\Enums\StatusImportacao::Concluida && $importacao->linhas_importadas > 0)
        <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
            @if ($importacao->temXmlParaDanfse())
                <a href="{{ route('importacoes.danfse', $importacao) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 font-semibold text-slate-700 shadow-sm hover:bg-slate-50">Baixar DANFSe (PDF)</a>
            @endif
            @if ($importacao->ehDocumentoFiscal())
                @php
                    $dataNota = $importacao->data_documento?->toDateString();
                    $dataNoPeriodo = $dataNota !== null && $dataNota >= config('simulacao.data_minima') && $dataNota <= config('simulacao.data_maxima');
                @endphp
                <a href="{{ route('simulacoes.create', array_filter([
                    'importacao' => $importacao->id,
                    'uf' => $importacao->uf?->value,
                    'municipio' => $importacao->municipio,
                    'data' => $dataNoPeriodo ? $dataNota : null,
                ])) }}" class="rounded-md bg-emerald-600 px-4 py-2 font-semibold text-white shadow-sm hover:bg-emerald-700">Simular esta nota</a>
            @endif
            <a href="{{ route('produtos.index') }}" class="text-emerald-700 hover:underline">Ver produtos →</a>
        </div>
        @if ($importacao->guardaXmlParaDanfse())
            <p class="mt-2 text-xs text-slate-500">
                @if ($importacao->temXmlParaDanfse())
                    O XML desta NFS-e fica guardado até {{ $importacao->xmlGuardadoAte()?->timezone('America/Sao_Paulo')->format('d/m/Y') }} para gerar o DANFSe; depois é apagado.
                @else
                    O XML desta NFS-e já foi apagado (guardado por {{ config('importacao.retencao_xml_dias') }} dias). Importe a nota de novo para gerar o DANFSe.
                @endif
            </p>
        @endif
        @error('simulacao')
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror
        @error('danfse')
            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror
    @endif

    @if ($erros->isNotEmpty())
        <section class="mt-8" aria-labelledby="titulo-erros">
            <h2 id="titulo-erros" class="text-lg font-semibold text-slate-900">Erros por {{ $importacao->ehDocumentoFiscal() ? 'item' : 'linha' }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                @if ($importacao->ehDocumentoFiscal())
                    Os itens abaixo não foram importados. Corrija o catálogo (por exemplo, cadastrando o produto de referência) e importe a nota de novo.
                @else
                    Corrija estas linhas na planilha e importe de novo: as linhas já importadas serão apenas atualizadas.
                @endif
            </p>
            @if ($importacao->linhas_com_erro > 0 && $erros->total() >= \App\Services\Importacao\ImportarCatalogoService::MAXIMO_ERROS_GRAVADOS)
                <p class="mt-1 text-xs text-amber-700">Mostrando os primeiros {{ \App\Services\Importacao\ImportarCatalogoService::MAXIMO_ERROS_GRAVADOS }} erros.</p>
            @endif
            <div class="relative mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">{{ $importacao->ehDocumentoFiscal() ? 'Item' : 'Linha' }}</th>
                            <th class="px-4 py-3">Campo</th>
                            <th class="px-4 py-3">Problema</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($erros as $erro)
                            <tr>
                                <td class="px-4 py-2 font-mono">{{ $erro->linha }}</td>
                                <td class="px-4 py-2">{{ $rotulos[$erro->campo] ?? $erro->campo ?? '—' }}</td>
                                <td class="px-4 py-2">{{ $erro->mensagem }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $erros->links() }}</div>
        </section>
    @endif
</x-layouts.app>
