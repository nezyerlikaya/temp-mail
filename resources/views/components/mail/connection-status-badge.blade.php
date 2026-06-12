@props(['status'])

@php
    $styles = [
        'not_tested' => 'border-stone-200 bg-stone-100 text-stone-700',
        'connected' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'failed' => 'border-red-200 bg-red-50 text-red-800',
        'disabled' => 'border-amber-200 bg-amber-50 text-amber-800',
    ];
    $labels = [
        'not_tested' => 'Not tested',
        'connected' => 'Connected',
        'failed' => 'Failed',
        'disabled' => 'Disabled',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-md border px-2.5 text-xs font-extrabold '.($styles[$status] ?? $styles['not_tested'])]) }}>
    {{ $labels[$status] ?? str($status)->headline() }}
</span>
