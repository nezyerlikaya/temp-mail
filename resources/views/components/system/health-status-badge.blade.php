@props(['status'])

@php
    $classes = match ($status) {
        'critical' => 'border-red-200 bg-red-50 text-red-800',
        'attention' => 'border-amber-200 bg-amber-50 text-amber-800',
        default => 'border-emerald-200 bg-emerald-50 text-emerald-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border px-2.5 text-xs font-extrabold '.$classes]) }}>
    {{ $status === 'attention' ? 'Needs attention' : str($status)->headline() }}
</span>
