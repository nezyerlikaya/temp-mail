@props(['label', 'value', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-5 shadow-sm']) }}>
    <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
    <p class="mt-2 break-words text-2xl font-extrabold text-stone-950">{{ $value }}</p>
    @if ($description)
        <p class="mt-2 text-xs font-bold text-stone-500">{{ $description }}</p>
    @endif
</div>
