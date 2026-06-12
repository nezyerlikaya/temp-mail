@props(['status'])

@php
    $styles = [
        'open' => 'border-red-200 bg-red-50 text-red-800',
        'reviewing' => 'border-amber-200 bg-amber-50 text-amber-900',
        'resolved' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'ignored' => 'border-stone-200 bg-stone-100 text-stone-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['open'])]) }}>
    {{ str($status)->headline() }}
</span>
