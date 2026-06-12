@props(['settings' => [], 'categories' => collect(), 'localeId' => null])

<div x-show="sectionType === 'blog_teaser'" class="space-y-4 rounded-lg border border-stone-200 bg-stone-50 p-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="blog-teaser-count" class="text-sm font-extrabold text-stone-800">Post count</label>
            <input id="blog-teaser-count" name="settings[post_count]" value="{{ old('settings.post_count', $settings['post_count'] ?? 3) }}" type="number" min="1" max="24" x-bind:disabled="sectionType !== 'blog_teaser'" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:input="dirty = true">
        </div>
        <div>
            <label for="blog-teaser-layout" class="text-sm font-extrabold text-stone-800">Layout readiness</label>
            <select id="blog-teaser-layout" name="settings[layout]" x-bind:disabled="sectionType !== 'blog_teaser'" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:change="dirty = true">
                @foreach (['grid' => 'Grid', 'list' => 'List', 'compact' => 'Compact'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('settings.layout', $settings['layout'] ?? 'grid') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label for="blog-teaser-category" class="text-sm font-extrabold text-stone-800">Category filter readiness</label>
        <select id="blog-teaser-category" name="settings[category_id]" x-bind:disabled="sectionType !== 'blog_teaser'" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:change="dirty = true">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('settings.category_id', $settings['category_id'] ?? '') === (string) $category->id)>{{ $category->name }} · {{ $category->locale?->locale }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs font-bold text-stone-500">This section references Blog Studio records later; it never owns posts.</p>
    </div>
</div>
