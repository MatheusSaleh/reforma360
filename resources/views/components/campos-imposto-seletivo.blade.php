@props(['valor', 'quantidade', 'data' => null])

<fieldset data-campos-is @if ($data) data-data="{{ $data }}" @endif class="mt-4 rounded-md border border-slate-200 p-4">
    <legend class="px-1 text-sm font-semibold text-slate-900">Imposto Seletivo</legend>
    <p class="mb-3 text-xs text-slate-600">Informe as alíquotas estimadas aplicáveis ao produto. Para a alíquota ad rem, informe também a unidade e a quantidade.</p>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-campo nome="is_aliquota_ad_valorem" rotulo="Alíquota ad valorem (%)" :valor="$valor('is_aliquota_ad_valorem')" inputmode="decimal" />
        <x-campo nome="is_aliquota_ad_rem" rotulo="Alíquota ad rem (R$ por unidade)" :valor="$valor('is_aliquota_ad_rem')" inputmode="decimal" />
        <x-campo nome="is_unidade" rotulo="Unidade do IS" :valor="$valor('is_unidade')" placeholder="LT" maxlength="6" />
        <x-campo nome="is_quantidade" :rotulo="$quantidade" :valor="$valor('is_quantidade')" inputmode="decimal" />
        <x-campo nome="is_cst" rotulo="CST do IS" :valor="$valor('is_cst')" placeholder="000" inputmode="numeric" maxlength="3" />
        <x-campo nome="is_c_class_trib" rotulo="cClassTrib do IS" :valor="$valor('is_c_class_trib')" placeholder="000001" inputmode="numeric" maxlength="6" />
    </div>
</fieldset>
