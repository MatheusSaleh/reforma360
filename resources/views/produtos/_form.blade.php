@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-campo nome="descricao" rotulo="Descrição" :valor="$produto->descricao" required maxlength="255" />
    </div>
    <x-campo nome="ncm" rotulo="NCM (mercadoria)" :valor="$produto->ncm" inputmode="numeric" ajuda="8 dígitos. Preencha o NCM ou o NBS." />
    <x-campo nome="nbs" rotulo="NBS (serviço)" :valor="$produto->nbs" inputmode="numeric" ajuda="9 dígitos." />
    <x-campo nome="cst" rotulo="CST" :valor="$produto->cst" inputmode="numeric" ajuda="3 dígitos. Ex.: 000, 200" required />
    <x-campo nome="c_class_trib" rotulo="cClassTrib" :valor="$produto->c_class_trib" inputmode="numeric" ajuda="6 dígitos. Ex.: 000001, 200028" required />
    <x-campo nome="preco" rotulo="Preço (R$)" :valor="$produto->preco" inputmode="decimal" placeholder="1.200,00" required />
</div>
<div class="mt-6 flex items-center gap-3">
    <x-botao>Salvar</x-botao>
    <a href="{{ route('produtos.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancelar</a>
</div>
