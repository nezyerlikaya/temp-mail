@props(['status'])

@php
    $styles = [
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'hidden' => 'border-stone-300 bg-stone-100 text-stone-700',
        'published' => 'border-teal-200 bg-teal-50 text-teal-800',
        'scheduled' => 'border-sky-200 bg-sky-50 text-sky-800',
        'trashed' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['draft'])]) }}>
    {{ str((string) $status)->headline() }}
</span>
