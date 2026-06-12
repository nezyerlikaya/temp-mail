<x-admin.layout title="Blog Studio" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Blog Studio"
        description="Language-specific publishing records for a WordPress-like editorial workflow."
    >
        <x-slot:actions>
            @if ($canCreatePost)
                <span class="inline-flex min-h-10 items-center rounded-lg border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700">Editor UI next</span>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Blog Studio summary">
            @foreach ([
                'Posts' => $summary['total'] ?? 0,
                'Draft' => $summary['draft'] ?? 0,
                'Published' => $summary['published'] ?? 0,
                'Scheduled' => $summary['scheduled'] ?? 0,
                'Trashed' => $summary['trashed'] ?? 0,
            ] as $label => $value)
                <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <main class="min-w-0 space-y-6">
                <x-blog.filter-bar :filters="$filters" :locales="$locales" :categories="$categories" :authors="$authors" :statuses="$statuses" />

                @if ($posts->count() > 0)
                    <section class="space-y-6" aria-labelledby="blog-results-title">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <h2 id="blog-results-title" class="text-lg font-extrabold text-stone-950">Post records</h2>
                                <p class="mt-1 text-sm text-stone-600">Search and filter by language, status, category, author, or date.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $posts->total() }} records</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($posts as $post)
                                <x-blog.post-card :post="$post" />
                            @endforeach
                        </div>

                        <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Post</th>
                                            <th scope="col" class="px-4 py-3">Language</th>
                                            <th scope="col" class="px-4 py-3">Category</th>
                                            <th scope="col" class="px-4 py-3">Status</th>
                                            <th scope="col" class="px-4 py-3">Readiness</th>
                                            <th scope="col" class="px-4 py-3">Author</th>
                                            <th scope="col" class="px-4 py-3">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($posts as $post)
                                            <x-blog.post-row :post="$post" />
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{ $posts->links() }}
                    </section>
                @else
                    <x-blog.empty-state />
                @endif
            </main>

            <aside class="min-w-0 space-y-6">
                <x-admin.card title="Publishing foundation" description="Blog Studio owns language-specific editorial records, not translations or global SEO.">
                    <div class="space-y-3 text-sm">
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Language-specific posts</p>
                            <p class="mt-1 text-stone-600">Each post, category, and tag belongs to one locale. Authors create separate records per language.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Media readiness</p>
                            <p class="mt-1 text-stone-600">Featured image fields are ready for Media Library picker integration in a later part.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Lifecycle readiness</p>
                            <p class="mt-1 text-stone-600">Publish, preview, trash, and restore workflows are intentionally prepared for upcoming Blog Studio steps.</p>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Recent posts" description="Latest Blog Studio records.">
                    @if ($recent->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recent as $post)
                                <div class="rounded-lg border border-stone-200 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-extrabold text-stone-950">{{ $post->title }}</p>
                                            <p class="mt-1 text-xs text-stone-500">{{ $post->locale?->language_name ?? 'Unknown language' }}</p>
                                        </div>
                                        <x-blog.status-badge :status="$post->status" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-blog.empty-state class="p-6" />
                    @endif
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin.layout>
