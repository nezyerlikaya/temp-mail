@props([
    'name',
    'label',
    'selected' => null,
    'assets' => [],
    'type' => 'all',
    'canSelect' => false,
    'canUpload' => false,
])

@php
    $id = str($name)->replace(['[', ']'], ['-', ''])->slug()->toString();
    $searchId = 'media-picker-search-'.$id;
    $typeId = 'media-picker-type-'.$id;
    $titleId = 'media-picker-title-'.$id;
    $oldValue = old($name);
    $initial = $selected ? [
        'id' => $selected['id'],
        'title' => $selected['title'],
        'original_name' => $selected['original_name'],
        'type' => $selected['type'],
        'mime_type' => $selected['mime_type'],
        'url' => $selected['url'],
        'usage_count' => $selected['usage_count'] ?? 0,
    ] : (filled($oldValue) ? [
        'id' => (int) $oldValue,
        'title' => 'Media #'.$oldValue,
        'original_name' => 'Previously selected media',
        'type' => 'image',
        'mime_type' => '',
        'url' => null,
        'usage_count' => 0,
    ] : null);
@endphp

<div
    x-data="{
        open: false,
        loading: false,
        query: '',
        type: @js($type),
        selected: @js($initial),
        assets: @js($assets),
        statusText: 'Media picker ready.',
        modalTitleId: @js($titleId),
        searchId: @js($searchId),
        typeId: @js($typeId),
        async search() {
            this.loading = true;
            const params = new URLSearchParams({ q: this.query, type: this.type || 'all' });
            const response = await fetch(@js(route('admin.media-library.picker')) + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await response.json();
            this.assets = payload.assets || [];
            this.loading = false;
            this.statusText = this.assets.length + ' media assets available.';
        },
        select(asset) {
            if (! @js($canSelect)) { return; }
            this.selected = asset;
            this.open = false;
            this.statusText = asset.title + ' selected.';
        },
        clear() {
            this.selected = null;
            this.statusText = 'Media selection cleared.';
        },
        canSelect: @js($canSelect)
        }
    }"
    class="space-y-3"
>
    <div class="flex items-center justify-between gap-3">
        <label for="{{ $id }}" class="text-sm font-extrabold text-stone-950">{{ $label }}</label>
        <input id="{{ $id }}" name="{{ $name }}" type="hidden" x-bind:value="selected ? selected.id : ''">
        @error($name)<span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
    </div>

    <x-media.selected-asset />

    <div class="flex flex-wrap gap-2">
        <button type="button" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" x-on:click="open = true; if (assets.length === 0) search()" @disabled(! $canSelect)>
            Choose media
        </button>
        <button type="button" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="clear()" x-bind:disabled="! selected || ! @js($canSelect)">
            Clear
        </button>
    </div>

    <x-media.picker-modal
        :search-url="route('admin.media-library.picker')"
        :search-id="$searchId"
        :type-id="$typeId"
        :can-upload="$canUpload"
    />
</div>
