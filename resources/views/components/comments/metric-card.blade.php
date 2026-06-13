@props(['label', 'value', 'description'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-5 shadow-sm']) }}>
    <p class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-extrabold text-stone-950">{{ $value }}</p>
    <p class="mt-1 text-sm text-stone-600">{{ $description }}</p>
</div>
