@props(['filters', 'locales', 'templateKeys', 'statuses'])

<form method="GET" action="{{ route('admin.email-templates.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-4">
        <label class="text-sm font-bold text-stone-700">
            <span>Language</span>
            <select name="locale_id" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All languages</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale->id }}" @selected((string) $filters['locale_id'] === (string) $locale->id)>{{ $locale->language_name }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-stone-700">
            <span>Template key</span>
            <select name="template_key" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All keys</option>
                @foreach ($templateKeys as $key => $label)
                    <option value="{{ $key }}" @selected($filters['template_key'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-stone-700">
            <span>Status</span>
            <select name="status" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All statuses</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-stone-700">
            <span>Missing readiness</span>
            <select name="missing" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All records</option>
                <option value="missing" @selected($filters['missing'] === 'missing')>Show missing queue</option>
            </select>
        </label>
    </div>
    <div class="mt-4 flex flex-wrap gap-2">
        <button class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Apply filters</button>
        <a href="{{ route('admin.email-templates.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Reset</a>
    </div>
</form>
