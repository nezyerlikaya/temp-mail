@props(['locale'])

<span class="inline-flex items-center gap-1 rounded-full border border-stone-200 bg-white px-2.5 py-1 text-xs font-extrabold text-stone-700">
    <span class="size-1.5 rounded-full bg-teal-500" aria-hidden="true"></span>
    {{ $locale?->language_name ?? 'Unknown language' }}
</span>
