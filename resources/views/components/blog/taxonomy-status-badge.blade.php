@props(['status'])

@php
    $styles = [
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'hidden' => 'border-amber-200 bg-amber-50 text-amber-800',
        'trashed' => 'border-stone-200 bg-stone-100 text-stone-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? 'border-stone-200 bg-stone-50 text-stone-700')]) }}>
    {{ str($status)->headline() }}
</span>
