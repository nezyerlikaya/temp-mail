@props(['default' => false])

@if ($default)
    <span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center rounded-md border border-teal-200 bg-teal-50 px-2.5 text-xs font-extrabold text-teal-800']) }}>Default</span>
@endif
