@props(['label', 'value', 'detail', 'icon' => 'inbox'])
<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div><p class="text-xs font-bold text-stone-500">{{ $label }}</p><p class="mt-2 text-2xl font-black text-stone-950">{{ $value }}</p></div>
        <span class="grid size-9 place-items-center rounded-md bg-teal-50 text-teal-800"><i data-lucide="{{ $icon }}" class="size-4" aria-hidden="true"></i></span>
    </div>
    <p class="mt-2 text-xs leading-5 text-stone-500">{{ $detail }}</p>
</article>
