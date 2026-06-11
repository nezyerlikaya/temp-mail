@props(['page', 'pageTypes' => []])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <a href="{{ route('admin.page-studio.edit', $page) }}" class="block focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-stone-950">{{ $page->title }}</p>
                <p class="mt-1 truncate font-mono text-xs text-stone-500">/{{ $page->slug }}</p>
            </div>
            <x-pages.status-badge :status="$page->status" />
        </div>

        <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-stone-600">{{ $page->excerpt ?: 'Summary readiness pending.' }}</p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-pages.language-badge :locale="$page->locale" />
            <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">{{ $pageTypes[$page->page_type] ?? str($page->page_type)->headline() }}</span>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-stone-500">
            <span>{{ $page->author?->name ?? 'System' }}</span>
            <span>{{ $page->created_at?->format('M j, Y') }}</span>
        </div>
    </a>
</article>
