@props([
    'filters' => [],
    'locales' => collect(),
    'categories' => collect(),
    'authors' => collect(),
    'statuses' => [],
])

<form method="GET" action="{{ route('admin.blog-studio.index') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_150px_170px_140px_150px_120px_auto]">
        <div>
            <label for="blog-q" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
            <input id="blog-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Title or slug" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>

        <div>
            <label for="blog-locale" class="text-xs font-extrabold uppercase text-stone-500">Language</label>
            <select id="blog-locale" name="locale_id" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($locales as $locale)
                    <option value="{{ $locale->id }}" @selected((string) ($filters['locale_id'] ?? 'all') === (string) $locale->id)>{{ $locale->language_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="blog-category" class="text-xs font-extrabold uppercase text-stone-500">Category</label>
            <select id="blog-category" name="category_id" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? 'all') === (string) $category->id)>{{ $category->name }} · {{ $category->locale?->locale }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="blog-status" class="text-xs font-extrabold uppercase text-stone-500">Status</label>
            <select id="blog-status" name="status" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="blog-author" class="text-xs font-extrabold uppercase text-stone-500">Author</label>
            <select id="blog-author" name="author_id" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all">All</option>
                @foreach ($authors as $author)
                    <option value="{{ $author->id }}" @selected((string) ($filters['author_id'] ?? 'all') === (string) $author->id)>{{ $author->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="blog-date" class="text-xs font-extrabold uppercase text-stone-500">Date</label>
            <select id="blog-date" name="date" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'today' => 'Today', 'week' => 'This week'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['date'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">Filter</button>
        </div>
    </div>
</form>
