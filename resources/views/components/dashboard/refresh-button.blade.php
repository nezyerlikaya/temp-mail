<button
    type="button"
    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-3 text-sm font-extrabold text-stone-800 transition hover:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:cursor-wait disabled:opacity-70"
    x-on:click="refresh()"
    x-bind:disabled="refreshing"
>
    <i data-lucide="refresh-cw" class="size-4" x-bind:class="{ 'animate-spin': refreshing }" aria-hidden="true"></i>
    <span x-text="refreshing ? 'Refreshing' : 'Refresh'">Refresh</span>
</button>
