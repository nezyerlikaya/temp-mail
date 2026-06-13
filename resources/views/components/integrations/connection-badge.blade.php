@props(['status' => 'not_configured'])

@php
    $styles = [
        'ready' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
        'connected' => 'bg-emerald-50 text-emerald-900 ring-emerald-100',
        'missing_configuration' => 'bg-amber-50 text-amber-900 ring-amber-100',
        'failed' => 'bg-red-50 text-red-900 ring-red-100',
        'not_configured' => 'bg-stone-100 text-stone-600 ring-stone-200',
    ];
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 {{ $styles[$status] ?? $styles['not_configured'] }}">
    {{ str($status)->replace('_', ' ')->headline() }}
</span>
