@props(['severity'])

@php
    $styles = [
        'info' => 'border-sky-200 bg-sky-50 text-sky-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'critical' => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2 py-1 text-xs font-extrabold '.$styles[$severity]]) }}>
    {{ str($severity)->headline() }}
</span>
