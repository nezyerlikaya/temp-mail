@props(['status'])

@php
    $styles = [
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'pending_dns' => 'border-amber-200 bg-amber-50 text-amber-900',
        'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'degraded' => 'border-orange-200 bg-orange-50 text-orange-900',
        'offline' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['draft'])]) }}>
    {{ str($status)->replace('_', ' ')->headline() }}
</span>
