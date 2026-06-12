@props(['locale'])

<span class="inline-flex items-center rounded-full border border-stone-200 bg-white px-2.5 py-1 text-xs font-extrabold text-stone-700">
    {{ $locale?->language_name ?? 'Unknown language' }}
</span>
