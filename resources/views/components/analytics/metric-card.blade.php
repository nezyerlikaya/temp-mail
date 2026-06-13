@props(['label', 'value', 'detail' => null])

<article {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ is_numeric($value) ? number_format((float) $value) : $value }}</p>
    @if($detail)
        <p class="mt-1 text-xs font-bold text-stone-500">{{ $detail }}</p>
    @endif
</article>
