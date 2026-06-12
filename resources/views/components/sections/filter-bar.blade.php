@props(['filters', 'locales', 'types', 'placements', 'statuses'])

<form method="GET" action="{{ route('admin.sections-studio.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_160px_180px_190px_140px_auto]">
        <div>
            <label for="section-q" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
            <input id="section-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Section title" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>

        <div>
            <label for="section-locale" class="text-xs font-extrabold uppercase text-stone-500">Language</label>
            <select id="section-locale" name="locale_id" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale->id }}" @selected((string) ($filters['locale_id'] ?? 'all') === (string) $locale->id)>{{ $locale->language_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="section-type" class="text-xs font-extrabold uppercase text-stone-500">Type</label>
            <select id="section-type" name="section_type" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['section_type'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="section-placement" class="text-xs font-extrabold uppercase text-stone-500">Placement</label>
            <select id="section-placement" name="placement" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($placements as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['placement'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="section-status" class="text-xs font-extrabold uppercase text-stone-500">Status</label>
            <select id="section-status" name="status" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">Filter</button>
        </div>
    </div>
</form>
