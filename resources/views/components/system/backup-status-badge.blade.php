@props(['status'])

@php
    $classes = $status === 'completed'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
        : 'border-red-200 bg-red-50 text-red-800';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-full border px-2.5 text-xs font-extrabold '.$classes]) }}>
    {{ str($status)->headline() }}
</span>
