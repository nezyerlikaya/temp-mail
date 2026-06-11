@props(['variant' => 'info', 'title' => null])

@php
    $styles = [
        'info' => 'border-sky-200 bg-sky-50 text-sky-950',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
        'danger' => 'border-red-200 bg-red-50 text-red-950',
    ];
@endphp

<div role="{{ $variant === 'danger' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'rounded-lg border p-4 '.$styles[$variant]]) }}>
    @if ($title)
        <p class="text-sm font-extrabold">{{ $title }}</p>
    @endif
    <div class="{{ $title ? 'mt-1' : '' }} text-sm leading-6">{{ $slot }}</div>
</div>
