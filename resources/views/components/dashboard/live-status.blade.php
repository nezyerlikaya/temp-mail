<div class="rounded-md border border-stone-200 bg-white px-3 py-2 shadow-sm" role="status" aria-live="polite">
    <div class="flex items-center gap-2">
        <span class="size-2 rounded-full bg-emerald-500" x-bind:class="{
            'bg-emerald-500': statusMessage === 'Live',
            'bg-stone-400': statusMessage === 'Paused',
            'bg-teal-500': statusMessage === 'Refreshing',
            'bg-red-500': statusMessage === 'Connection unavailable'
        }"></span>
        <p class="text-sm font-extrabold text-stone-950" x-text="statusMessage">Live</p>
        <i data-lucide="loader-circle" class="size-4 animate-spin text-teal-700" x-show="refreshing" aria-hidden="true"></i>
    </div>
    <p class="mt-1 text-xs font-semibold text-stone-500">
        <span x-text="payload.last_updated_display">{{ now()->format('H:i:s') }}</span>
        <span aria-hidden="true">.</span>
        <span x-text="intervalSeconds + 's interval'">30s interval</span>
    </p>
</div>
