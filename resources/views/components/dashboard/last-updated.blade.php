@props(['timestamp', 'seconds' => 20])

<div class="rounded-md border border-stone-200 bg-white px-3 py-2 text-right shadow-sm">
    <p class="text-xs font-extrabold uppercase text-stone-500">Last calculated</p>
    <p class="text-sm font-extrabold text-stone-950">{{ $timestamp->format('H:i:s') }}</p>
    <p class="text-xs font-semibold text-stone-500">{{ $seconds }}s cache</p>
</div>
