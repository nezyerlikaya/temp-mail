@props(['status'])

@php
    $styles = [
        'configured' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'passive' => 'border-stone-200 bg-stone-50 text-stone-700',
        'needs_key' => 'border-amber-200 bg-amber-50 text-amber-900',
        'failed' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['passive'])]) }}>
    {{ str($status)->replace('_', ' ')->headline() }}
</span>
