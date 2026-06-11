@props(['emptyLabel' => 'No media selected'])

<div class="rounded-lg border border-stone-200 bg-stone-50 p-3">
    <template x-if="selected">
        <div class="flex items-center gap-3">
            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-stone-200 bg-white">
                <template x-if="selected && selected.mime_type && selected.mime_type.startsWith('image/')">
                    <img x-bind:src="selected.url" alt="" class="h-full w-full object-cover">
                </template>
                <template x-if="! selected || ! selected.mime_type || ! selected.mime_type.startsWith('image/')">
                    <div class="flex h-full w-full items-center justify-center text-[11px] font-bold uppercase text-stone-500" x-text="selected ? selected.type : ''"></div>
                </template>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-extrabold text-stone-950" x-text="selected.title"></p>
                <p class="mt-1 truncate text-xs text-stone-500" x-text="selected.original_name"></p>
            </div>
            <span class="rounded-full border px-2.5 py-1 text-xs font-bold" x-bind:class="selected && selected.usage_count > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900'" x-text="selected && selected.usage_count > 0 ? selected.usage_count + ' uses' : 'Orphaned'"></span>
        </div>
    </template>

    <template x-if="! selected">
        <div class="flex min-h-14 items-center justify-center text-center text-sm font-bold text-stone-500">
            {{ $emptyLabel }}
        </div>
    </template>
</div>
