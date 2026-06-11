@props(['filters' => [], 'uploadTargets' => []])

<form method="GET" action="{{ route('admin.media-library.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_130px_130px_130px_120px_120px_auto]">
        <div>
            <label for="media-q" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
            <input id="media-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="File name, title, alt text" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="media-type" class="text-xs font-extrabold uppercase text-stone-500">Type</label>
            <select id="media-type" name="type" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All'] + $uploadTargets as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="media-status" class="text-xs font-extrabold uppercase text-stone-500">Status</label>
            <select id="media-status" name="status" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'active' => 'Active', 'hidden' => 'Hidden', 'trashed' => 'Trashed'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="media-usage" class="text-xs font-extrabold uppercase text-stone-500">Usage</label>
            <select id="media-usage" name="usage" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'orphaned' => 'Orphaned', 'in-use' => 'In use'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['usage'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="media-uploader" class="text-xs font-extrabold uppercase text-stone-500">Uploader</label>
            <input id="media-uploader" name="uploader" value="{{ $filters['uploader'] ?? '' }}" inputmode="numeric" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="media-date" class="text-xs font-extrabold uppercase text-stone-500">Date</label>
            <select id="media-date" name="date" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'today' => 'Today', 'week' => 'This week'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['date'] ?? 'all') === $value)>{{ $label }}</option>
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
