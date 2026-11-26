@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-campo nome="descricao" rotulo="Descrição" :valor="$produto->descricao" required maxlength="255" />
    </div>
    <x-campo-nomenclatura tipo="ncm" rotulo="NCM (mercadoria)" :valor="$produto->ncm" placeholder="Código ou descrição" ajuda="Preencha o NCM ou o NBS." />
    <x-campo-nomenclatura tipo="nbs" rotulo="NBS (serviço)" :valor="$produto->nbs" placeholder="Código ou descrição" />
    <x-campos-classificacao :cst="$produto->cst" :c-class-trib="$produto->c_class_trib" />
    <x-campo nome="preco" rotulo="Preço (R$)" :valor="$produto->preco" inputmode="decimal" placeholder="1.200,00" required />
</div>
<div class="mt-6 flex items-center gap-3">
    <x-botao>Salvar</x-botao>
    <a href="{{ route('produtos.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancelar</a>
</div>
