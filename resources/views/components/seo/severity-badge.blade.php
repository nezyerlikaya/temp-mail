@props(['severity' => 'notice'])

@php
    $classes = [
        'critical' => 'border-red-200 bg-red-50 text-red-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'notice' => 'border-sky-200 bg-sky-50 text-sky-700',
        'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ][$severity] ?? 'border-stone-200 bg-stone-50 text-stone-700';
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md border px-2 py-1 text-xs font-extrabold', $classes]) }}>
    {{ str($severity)->headline() }}
</span>
