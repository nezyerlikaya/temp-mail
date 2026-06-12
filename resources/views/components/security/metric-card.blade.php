@props(['label', 'value', 'tone' => 'neutral'])

@php
    $tones = [
        'critical' => 'border-red-200 bg-red-50 text-red-950',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
        'positive' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
        'neutral' => 'border-stone-200 bg-white text-stone-950',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 shadow-sm '.($tones[$tone] ?? $tones['neutral'])]) }}>
    <p class="text-xs font-extrabold uppercase text-current opacity-70">{{ $label }}</p>
    <p class="mt-3 text-2xl font-extrabold">{{ number_format($value) }}</p>
</div>
