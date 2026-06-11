@props(['status'])

@php
    $styles = [
        'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'scheduled' => 'bg-sky-100 text-sky-800 ring-sky-200',
        'expired' => 'bg-stone-100 text-stone-700 ring-stone-200',
        'cancelled' => 'bg-red-100 text-red-800 ring-red-200',
        'none' => 'bg-stone-100 text-stone-600 ring-stone-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset '.($styles[$status] ?? $styles['none'])]) }}>
    {{ str($status)->headline() }}
</span>
