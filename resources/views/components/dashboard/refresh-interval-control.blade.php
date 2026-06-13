<div class="flex flex-wrap items-center gap-2 rounded-md border border-stone-200 bg-white p-2">
    <button
        type="button"
        class="inline-flex min-h-9 items-center justify-center rounded-md px-3 text-xs font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20"
        x-bind:class="autoRefresh ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-100' : 'bg-stone-100 text-stone-700'"
        x-on:click="setAutoRefresh(! autoRefresh)"
        x-bind:aria-pressed="autoRefresh.toString()"
    >
        <span x-text="autoRefresh ? 'Auto-refresh on' : 'Auto-refresh off'">Auto-refresh on</span>
    </button>

    <label for="dashboard-refresh-interval" class="sr-only">Refresh interval</label>
    <select
        id="dashboard-refresh-interval"
        class="min-h-9 rounded-md border border-stone-300 bg-white px-2 text-xs font-extrabold text-stone-800 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20"
        x-model.number="intervalSeconds"
        x-on:change="setIntervalSeconds(intervalSeconds)"
    >
        <template x-for="interval in allowedIntervals" x-bind:key="interval">
            <option x-bind:value="interval" x-text="interval + ' seconds'"></option>
        </template>
    </select>
</div>
