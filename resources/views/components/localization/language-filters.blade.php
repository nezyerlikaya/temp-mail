@props(['filters'])

<form method="GET" action="{{ route('admin.locale-launch-center.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_160px_150px_150px_120px_auto]">
        <div>
            <label for="locale-q" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
            <input id="locale-q" name="q" value="{{ $filters['q'] }}" placeholder="Language, locale, market" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>

        <div>
            <label for="locale-state" class="text-xs font-extrabold uppercase text-stone-500">State</label>
            <select id="locale-state" name="state" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'active' => 'Active', 'passive' => 'Passive'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['state'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="locale-direction" class="text-xs font-extrabold uppercase text-stone-500">Direction</label>
            <select id="locale-direction" name="direction" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'ltr' => 'LTR', 'rtl' => 'RTL'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['direction'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="locale-status" class="text-xs font-extrabold uppercase text-stone-500">Status</label>
            <select id="locale-status" name="status" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'draft' => 'Draft', 'ready' => 'Ready', 'launched' => 'Launched', 'paused' => 'Paused'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="locale-per-page" class="text-xs font-extrabold uppercase text-stone-500">View</label>
            <select id="locale-per-page" name="per_page" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach ([10, 20, 30] as $value)
                    <option value="{{ $value }}" @selected((int) $filters['per_page'] === $value)>{{ $value }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                Filter
            </button>
        </div>
    </div>
</form>
