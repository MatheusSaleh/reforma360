@php
    $cores = [
        'pendente' => 'bg-slate-100 text-slate-700',
        'processando' => 'bg-sky-100 text-sky-800',
        'concluida' => 'bg-emerald-100 text-emerald-800',
        'concluida_com_erros' => 'bg-amber-100 text-amber-800',
        'falhou' => 'bg-red-100 text-red-800',
    ];
@endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cores[$status->value] }}" data-status-simulacao>{{ $status->label() }}</span>
