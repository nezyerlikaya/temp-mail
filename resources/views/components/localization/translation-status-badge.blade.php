@props(['status'])

@php
    $styles = [
        'draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'ready' => 'border-teal-200 bg-teal-50 text-teal-800',
        'launched' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'paused' => 'border-amber-200 bg-amber-50 text-amber-900',
        'Live' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'In Review' => 'border-teal-200 bg-teal-50 text-teal-800',
        'Draft' => 'border-stone-200 bg-stone-50 text-stone-700',
        'Offline' => 'border-amber-200 bg-amber-50 text-amber-900',
        'planned' => 'border-stone-200 bg-stone-50 text-stone-700',
        'blocked' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.($styles[$status] ?? $styles['draft'])]) }}>
    {{ str((string) $status)->headline() }}
</span>
