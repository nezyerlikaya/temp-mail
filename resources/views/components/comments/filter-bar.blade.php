@props(['filters', 'posts', 'locales'])

<form method="GET" action="{{ route('admin.comment-moderation.index') }}" {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }}>
    <div class="grid gap-4 xl:grid-cols-[minmax(220px,1.5fr)_repeat(6,minmax(120px,1fr))]">
        <div>
            <label for="comment-search" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
            <input id="comment-search" name="q" value="{{ $filters['q'] }}" type="search" placeholder="Author or excerpt" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="comment-post" class="text-xs font-extrabold uppercase text-stone-500">Post</label>
            <select id="comment-post" name="post_id" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="">All posts</option>
                @foreach ($posts as $id => $title)
                    <option value="{{ $id }}" @selected((string) $filters['post_id'] === (string) $id)>{{ $title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="comment-locale" class="text-xs font-extrabold uppercase text-stone-500">Language</label>
            <select id="comment-locale" name="locale_id" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="">All</option>
                @foreach ($locales as $id => $label)
                    <option value="{{ $id }}" @selected((string) $filters['locale_id'] === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="comment-score" class="text-xs font-extrabold uppercase text-stone-500">Spam score</label>
            <select id="comment-score" name="spam_score" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected($filters['spam_score'] === 'all')>All</option>
                <option value="high" @selected($filters['spam_score'] === 'high')>High</option>
                <option value="low" @selected($filters['spam_score'] === 'low')>Low</option>
            </select>
        </div>
        <div>
            <label for="comment-links" class="text-xs font-extrabold uppercase text-stone-500">Links</label>
            <select id="comment-links" name="has_links" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <option value="all" @selected($filters['has_links'] === 'all')>All</option>
                <option value="yes" @selected($filters['has_links'] === 'yes')>Has links</option>
                <option value="no" @selected($filters['has_links'] === 'no')>No links</option>
            </select>
        </div>
        <div>
            <label for="comment-akismet" class="text-xs font-extrabold uppercase text-stone-500">Akismet</label>
            <select id="comment-akismet" name="akismet" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All', 'passive' => 'Passive', 'configured' => 'Configured', 'hold' => 'Hold', 'spam' => 'Spam', 'unavailable' => 'Unavailable'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['akismet'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="comment-date" class="text-xs font-extrabold uppercase text-stone-500">Date</label>
            <input id="comment-date" name="date" value="{{ $filters['date'] }}" type="date" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        </div>
    </div>
    <div class="mt-4 flex gap-3">
        <button class="inline-flex min-h-11 items-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Apply filters</button>
        <a href="{{ route('admin.comment-moderation.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Reset</a>
    </div>
</form>
