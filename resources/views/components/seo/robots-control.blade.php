@props(['record'])

<x-admin.card title="Robots directives" description="Indexing state is explicit to avoid accidental noindex.">
    <div class="space-y-4">
        <label class="flex min-h-11 items-center justify-between rounded-lg border border-stone-200 px-3">
            <span class="text-sm font-extrabold text-stone-800">Allow indexing</span>
            <input type="hidden" name="robots_index" value="0">
            <input type="checkbox" name="robots_index" value="1" x-model="robotsIndex" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
        </label>
        <label class="flex min-h-11 items-center justify-between rounded-lg border border-stone-200 px-3">
            <span class="text-sm font-extrabold text-stone-800">Allow following links</span>
            <input type="hidden" name="robots_follow" value="0">
            <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $record->robots_follow)) class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
        </label>
        <div x-cloak x-show="! robotsIndex" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-900" role="alert">
            Noindex is active. This target may disappear from search results.
        </div>
    </div>
</x-admin.card>
