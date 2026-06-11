@props(['status'])

@php
    $normalizedStatus = strtolower($status);
    $styles = [
        'active' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'published' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'draft' => 'bg-stone-100 text-stone-700 ring-stone-200',
        'scheduled' => 'bg-sky-100 text-sky-800 ring-sky-200',
        'hidden' => 'bg-amber-100 text-amber-900 ring-amber-200',
        'locked' => 'bg-red-100 text-red-800 ring-red-200',
        'archived' => 'bg-stone-200 text-stone-700 ring-stone-300',
        'expired' => 'bg-red-100 text-red-800 ring-red-200',
        'trashed' => 'bg-red-100 text-red-800 ring-red-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset '.($styles[$normalizedStatus] ?? $styles['draft'])]) }}>
    {{ str($status)->headline() }}
</span>
