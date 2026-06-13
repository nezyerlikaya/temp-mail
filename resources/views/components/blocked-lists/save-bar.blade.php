@props(['editing' => false])
<div class="sticky bottom-4 mt-4 flex items-center justify-between gap-3 rounded-lg border border-stone-200 bg-white/95 p-3 shadow-lg backdrop-blur">
    <p class="text-xs font-semibold text-stone-600">{{ $editing ? 'Save updates to this reviewed rule.' : 'Create a manual reviewed rule.' }}</p>
    <button class="inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">{{ $editing ? 'Save entry' : 'Create entry' }}</button>
</div>
