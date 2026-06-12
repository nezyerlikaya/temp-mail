@props(['filters', 'locales', 'targetTypes'])

<form method="GET" action="{{ route('admin.seo-growth-center.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[170px_210px_190px_160px_160px_auto]">
        <div>
            <label for="seo-locale" class="text-xs font-extrabold uppercase text-stone-500">Language</label>
            <select id="seo-locale" name="locale" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale->locale }}" @selected(($filters['locale'] ?? 'all') === $locale->locale)>{{ $locale->language_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="seo-target-type" class="text-xs font-extrabold uppercase text-stone-500">Target type</label>
            <select id="seo-target-type" name="target_type" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($targetTypes as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['target_type'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="seo-missing" class="text-xs font-extrabold uppercase text-stone-500">Metadata</label>
            <select id="seo-missing" name="missing_metadata" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['missing_metadata'] ?? 'all') === 'all')>All</option>
                <option value="missing" @selected(($filters['missing_metadata'] ?? 'all') === 'missing')>Missing title/description</option>
            </select>
        </div>

        <div>
            <label for="seo-robots" class="text-xs font-extrabold uppercase text-stone-500">Robots</label>
            <select id="seo-robots" name="robots" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['robots'] ?? 'all') === 'all')>All</option>
                <option value="index" @selected(($filters['robots'] ?? 'all') === 'index')>Index</option>
                <option value="noindex" @selected(($filters['robots'] ?? 'all') === 'noindex')>Noindex</option>
            </select>
        </div>

        <div>
            <label for="seo-sitemap" class="text-xs font-extrabold uppercase text-stone-500">Sitemap</label>
            <select id="seo-sitemap" name="sitemap" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['sitemap'] ?? 'all') === 'all')>All</option>
                <option value="included" @selected(($filters['sitemap'] ?? 'all') === 'included')>Included</option>
                <option value="excluded" @selected(($filters['sitemap'] ?? 'all') === 'excluded')>Excluded</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">Filter</button>
        </div>
    </div>
</form>
