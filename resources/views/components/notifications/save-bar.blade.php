@props(['disabled' => false])

<div class="sticky bottom-4 z-20 mt-5 flex flex-col gap-3 rounded-lg border border-stone-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm font-bold text-stone-600">Rule changes affect admin delivery only. Critical alerts stay protected.</p>
    <button
        type="submit"
        @disabled($disabled)
        class="inline-flex min-h-11 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:bg-stone-400"
    >
        Save notification rules
    </button>
</div>
