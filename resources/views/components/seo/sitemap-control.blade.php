@props(['record', 'frequencies'])

<x-admin.card title="Sitemap controls" description="Sitemap generation is later; these flags are ready now.">
    <div class="space-y-4">
        <label class="flex min-h-11 items-center justify-between rounded-lg border border-stone-200 px-3">
            <span class="text-sm font-extrabold text-stone-800">Include in sitemap</span>
            <input type="hidden" name="include_in_sitemap" value="0">
            <input type="checkbox" name="include_in_sitemap" value="1" @checked(old('include_in_sitemap', $record->include_in_sitemap)) class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
        </label>
        <div>
            <label for="seo-sitemap-priority" class="text-sm font-extrabold text-stone-800">Priority</label>
            <input id="seo-sitemap-priority" name="sitemap_priority" value="{{ old('sitemap_priority', $record->sitemap_priority) }}" type="number" min="0" max="1" step="0.1" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="seo-change-frequency" class="text-sm font-extrabold text-stone-800">Change frequency</label>
            <select id="seo-change-frequency" name="sitemap_change_frequency" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach ($frequencies as $value => $label)
                    <option value="{{ $value }}" @selected(old('sitemap_change_frequency', $record->sitemap_change_frequency) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</x-admin.card>
