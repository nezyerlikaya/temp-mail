@props(['status'])

@php
    $styles = [
        'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'suspended' => 'bg-red-100 text-red-800 ring-red-200',
        'invited' => 'bg-sky-100 text-sky-800 ring-sky-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset '.($styles[$status] ?? 'bg-stone-100 text-stone-700 ring-stone-200')]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ str($status)->headline() }}
</span>
