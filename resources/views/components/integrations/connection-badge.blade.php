@props(['status' => 'not_tested'])

@php
    $styles = [
        'connected' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
        'degraded' => 'bg-amber-50 text-amber-900 ring-amber-100',
        'failed' => 'bg-red-50 text-red-900 ring-red-100',
        'disabled' => 'bg-stone-100 text-stone-600 ring-stone-200',
        'not_tested' => 'bg-sky-50 text-sky-900 ring-sky-100',
    ];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 {{ $styles[$status] ?? $styles['not_tested'] }}">
    {{ str($status)->replace('_', ' ')->headline() }}
</span>
