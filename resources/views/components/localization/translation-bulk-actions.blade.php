@props(['canReview', 'canPublish'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3 rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <label class="inline-flex items-center gap-2 text-sm font-extrabold text-stone-700">
        <input type="checkbox" x-model="selectAll" x-on:change="toggleAll()" class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
        Select all visible
    </label>
    <span class="text-sm font-semibold text-stone-500" x-text="selectedCount + ' selected'"></span>

    <div class="ml-auto flex flex-wrap gap-2">
        @if ($canReview)
            <button type="submit" formaction="{{ route('admin.translation-center.translations.review') }}" class="inline-flex min-h-10 items-center rounded-lg border border-sky-300 bg-sky-50 px-4 py-2 text-sm font-extrabold text-sky-900 focus:outline-none focus:ring-4 focus:ring-sky-600/20">
                Mark reviewed
            </button>
        @endif
        @if ($canPublish)
            <button type="submit" formaction="{{ route('admin.translation-center.translations.publish') }}" class="inline-flex min-h-10 items-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-extrabold text-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-600/20">
                Publish selected
            </button>
        @endif
    </div>
</div>
