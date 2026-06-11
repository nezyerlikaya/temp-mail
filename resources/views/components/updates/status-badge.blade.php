@props(['status'])

@php
    $styles = [
        'passed' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'available' => 'border-teal-200 bg-teal-50 text-teal-800',
        'current' => 'border-sky-200 bg-sky-50 text-sky-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'incompatible' => 'border-amber-200 bg-amber-50 text-amber-900',
        'failed' => 'border-red-200 bg-red-50 text-red-800',
        'locked' => 'border-red-200 bg-red-50 text-red-800',
        'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'pending' => 'border-stone-200 bg-stone-50 text-stone-700',
    ];
    $key = (string) $status;
    $normalized = str_replace('_', ' ', $key);
    $class = $styles[$key] ?? $styles['pending'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-extrabold '.$class]) }}>
    {{ str($normalized)->headline() }}
</span>
