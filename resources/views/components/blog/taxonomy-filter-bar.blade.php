@props(['filters', 'locales', 'statuses'])

<form method="GET" action="{{ route('admin.taxonomy.index') }}" {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'categories' }}">

    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_180px_160px_auto] md:items-end">
        <div>
            <label for="taxonomy-q" class="text-sm font-extrabold text-stone-800">Search</label>
            <input
                id="taxonomy-q"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                type="search"
                autocomplete="off"
                class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                placeholder="Name or slug"
            >
        </div>

        <div>
            <label for="taxonomy-locale" class="text-sm font-extrabold text-stone-800">Language</label>
            <select id="taxonomy-locale" name="locale_id" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm font-bold text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All languages</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale->id }}" @selected((string) ($filters['locale_id'] ?? 'all') === (string) $locale->id)>{{ $locale->language_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="taxonomy-status" class="text-sm font-extrabold text-stone-800">Status</label>
            <select id="taxonomy-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm font-bold text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
            Filter
        </button>
    </div>
</form>
