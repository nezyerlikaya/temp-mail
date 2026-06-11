@props(['status'])

@php
    $styles = [
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'hidden' => 'border-stone-300 bg-stone-100 text-stone-700',
        'trashed' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['hidden'])]) }}>
    {{ str((string) $status)->headline() }}
</span>
