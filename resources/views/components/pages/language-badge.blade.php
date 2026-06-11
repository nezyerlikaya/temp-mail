@props(['locale'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-2.5 py-1 text-xs font-extrabold text-stone-700']) }}>
    <span class="text-stone-950">{{ strtoupper($locale?->locale ?? 'NA') }}</span>
    <span class="font-bold text-stone-500">{{ $locale?->direction === 'rtl' ? 'RTL' : 'LTR' }}</span>
</span>
