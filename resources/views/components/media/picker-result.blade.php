<button
    type="button"
    class="flex w-full items-center gap-3 rounded-lg border border-stone-200 bg-white p-3 text-left transition hover:border-teal-300 hover:bg-teal-50/40 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
    x-on:click="select(asset)"
    x-bind:aria-label="'Select ' + asset.title"
    x-bind:disabled="! canSelect"
>
    <span class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-stone-200 bg-stone-50">
        <template x-if="asset.mime_type && asset.mime_type.startsWith('image/')">
            <img x-bind:src="asset.url" alt="" class="h-full w-full object-cover">
        </template>
        <template x-if="! asset.mime_type || ! asset.mime_type.startsWith('image/')">
            <span class="flex h-full w-full items-center justify-center text-[11px] font-bold uppercase text-stone-500" x-text="asset.type"></span>
        </template>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-extrabold text-stone-950" x-text="asset.title"></span>
        <span class="mt-1 block truncate text-xs text-stone-500" x-text="asset.original_name"></span>
    </span>
    <span class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-bold text-stone-600" x-text="asset.usage_count + ' uses'"></span>
</button>
