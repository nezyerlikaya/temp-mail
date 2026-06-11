@props(['status'])

@php
    $styles = [
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['draft'])]) }}>
    {{ str((string) $status)->headline() }}
</span>
