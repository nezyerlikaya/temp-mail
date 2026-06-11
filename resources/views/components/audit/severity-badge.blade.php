@props(['severity'])

@php
    $classes = match ($severity) {
        'critical' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        default => 'border-teal-200 bg-teal-50 text-teal-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border px-2.5 text-xs font-extrabold '.$classes]) }}>
    {{ str($severity)->headline() }}
</span>
