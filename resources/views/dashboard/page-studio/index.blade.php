<x-admin.layout title="Page Studio" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Page Studio"
        description="Language-specific page records for legal, contact, pricing, API docs, and future publishing workflows."
    >
        <x-slot:actions>
            @if ($canCreatePage)
                <x-pages.trash-filter :active="($filters['status'] ?? 'all') === 'trashed'" />
                <a href="{{ route('admin.page-studio.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                    Create page
                </a>
            @else
                <x-pages.trash-filter :active="($filters['status'] ?? 'all') === 'trashed'" />
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Page Studio summary">
            @foreach ([
                'Pages' => $summary['total'] ?? 0,
                'Draft' => $summary['draft'] ?? 0,
                'Published' => $summary['published'] ?? 0,
                'Trashed' => $summary['trashed'] ?? 0,
                'Legal' => $summary['legal'] ?? 0,
            ] as $label => $value)
                <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <main class="min-w-0 space-y-6">
                <x-pages.filter-bar :filters="$filters" :locales="$locales" :authors="$authors" :page-types="$pageTypes" :statuses="$statuses" />

                @if ($pages->count() > 0)
                    <section class="space-y-6" aria-labelledby="page-results-title">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <h2 id="page-results-title" class="text-lg font-extrabold text-stone-950">Page records</h2>
                                <p class="mt-1 text-sm text-stone-600">Search and filter by language, type, status, author, or date.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $pages->total() }} records</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($pages as $page)
                                <x-pages.page-card :page="$page" :page-types="$pageTypes" :preview-url="$previewUrls[$page->id] ?? null" :legal-readiness="$legalReadiness[$page->id] ?? null" :can-preview="$canPreviewPage" />
                            @endforeach
                        </div>

                        <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Page</th>
                                            <th scope="col" class="px-4 py-3">Language</th>
                                            <th scope="col" class="px-4 py-3">Type</th>
                                            <th scope="col" class="px-4 py-3">Status</th>
                                            <th scope="col" class="px-4 py-3">Readiness</th>
                                            <th scope="col" class="px-4 py-3">Author</th>
                                            <th scope="col" class="px-4 py-3">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pages as $page)
                                            <x-pages.page-row :page="$page" :page-types="$pageTypes" :preview-url="$previewUrls[$page->id] ?? null" :legal-readiness="$legalReadiness[$page->id] ?? null" :can-preview="$canPreviewPage" />
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{ $pages->links() }}
                    </section>
                @else
                    <x-pages.empty-state />
                @endif
            </main>

            <aside class="min-w-0 space-y-6">
                <x-admin.card title="Publishing readiness" description="Lifecycle, preview, and legal mapping hooks stay in Page Studio while public theme rendering remains separate.">
                    <div class="space-y-3 text-sm">
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Language-specific records</p>
                            <p class="mt-1 text-stone-600">Each page belongs to one locale. No translation relationship is created.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Legal page readiness</p>
                            <p class="mt-1 text-stone-600">Settings maps legal records; Page Studio owns the language-specific page content.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Signed preview</p>
                            <p class="mt-1 text-stone-600">Preview links are temporary and signed so unpublished pages stay protected.</p>
                        </div>
                    </div>
                </x-admin.card>

                <x-admin.card title="Recent pages" description="Latest Page Studio records.">
                    @if ($recent->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recent as $page)
                                <a href="{{ route('admin.page-studio.edit', $page) }}" class="block rounded-lg border border-stone-200 p-3 hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-extrabold text-stone-950">{{ $page->title }}</p>
                                            <p class="mt-1 text-xs text-stone-500">{{ $page->locale?->language_name ?? 'Unknown language' }}</p>
                                        </div>
                                        <x-pages.status-badge :status="$page->status" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <x-pages.empty-state class="p-6" />
                    @endif
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin.layout>
