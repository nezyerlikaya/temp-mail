@props(['filters', 'statuses'])

<form method="GET" action="{{ route('admin.domains.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
        <label class="grid gap-2 xl:col-span-2">
            <span class="text-xs font-extrabold uppercase text-stone-500">Search</span>
            <input name="q" value="{{ $filters['q'] }}" placeholder="domain.com or label" class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </label>

        @foreach ([['status', 'Status', $statuses], ['active', 'State', ['active' => 'Active', 'passive' => 'Passive']], ['visibility', 'Availability', ['public' => 'Public', 'private' => 'Private']], ['dns', 'DNS readiness', ['ready' => 'Ready', 'needs_dns' => 'Needs DNS']]] as [$field, $label, $options])
            <label class="grid gap-2">
                <span class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</span>
                <select name="{{ $field }}" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="all">All</option>
                    @foreach ($options as $value => $optionLabel)
                        <option value="{{ $value }}" @selected($filters[$field] === $value)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <button class="inline-flex min-h-10 items-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Apply filters</button>
        <a href="{{ route('admin.domains.index') }}" class="inline-flex min-h-10 items-center rounded-md border border-stone-300 bg-white px-4 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-stone-500/20">Reset</a>
    </div>
</form>
