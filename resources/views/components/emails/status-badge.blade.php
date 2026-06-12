@props(['status' => 'draft'])

@php
    $classes = [
        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'draft' => 'border-amber-200 bg-amber-50 text-amber-800',
        'hidden' => 'border-stone-200 bg-stone-100 text-stone-700',
    ][$status] ?? 'border-stone-200 bg-stone-50 text-stone-700';
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md border px-2 py-1 text-xs font-extrabold', $classes]) }}>
    {{ str($status)->headline() }}
</span>
