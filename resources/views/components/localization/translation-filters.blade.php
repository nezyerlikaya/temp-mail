@props(['filters', 'groups'])

<form method="GET" action="{{ route('admin.translation-center.index') }}" {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <div class="grid gap-4 lg:grid-cols-[minmax(220px,1.5fr)_repeat(5,minmax(140px,1fr))]">
        <div>
            <label for="translation-search" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Search</label>
            <input
                id="translation-search"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                type="search"
                placeholder="Key or English source"
                class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            >
        </div>

        <div>
            <label for="translation-group" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Group</label>
            <select id="translation-group" name="group" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['group'] ?? 'all') === 'all')>All groups</option>
                @foreach ($groups as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['group'] ?? 'all') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="translation-requirement" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Requirement</label>
            <select id="translation-requirement" name="requirement" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['requirement'] ?? 'all') === 'all')>All</option>
                <option value="required" @selected(($filters['requirement'] ?? 'all') === 'required')>Required</option>
                <option value="optional" @selected(($filters['requirement'] ?? 'all') === 'optional')>Optional</option>
            </select>
        </div>

        <div>
            <label for="translation-state" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">State</label>
            <select id="translation-state" name="state" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['state'] ?? 'all') === 'all')>All</option>
                <option value="active" @selected(($filters['state'] ?? 'all') === 'active')>Active</option>
                <option value="passive" @selected(($filters['state'] ?? 'all') === 'passive')>Passive</option>
            </select>
        </div>

        <div>
            <label for="translation-missing" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Readiness</label>
            <select id="translation-missing" name="missing" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected(($filters['missing'] ?? 'all') === 'all')>All</option>
                <option value="missing" @selected(($filters['missing'] ?? 'all') === 'missing')>Missing translations</option>
            </select>
        </div>

        <div>
            <label for="translation-per-page" class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Per page</label>
            <select id="translation-per-page" name="per_page" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach ([6, 12, 24, 48] as $size)
                    <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 12) === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-3">
        <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
            Apply filters
        </button>
        <a href="{{ route('admin.translation-center.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            Reset
        </a>
    </div>
</form>
