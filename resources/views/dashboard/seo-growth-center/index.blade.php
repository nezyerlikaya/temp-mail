<x-admin.layout title="SEO Growth Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="SEO Growth Center"
        description="Target-specific, language-specific metadata readiness for search, social cards, robots, sitemap, and schema."
    >
        <x-slot:actions>
            @if ($canUpdateSeo)
                <a href="{{ route('admin.seo-growth-center.records.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm focus:outline-none focus:ring-4 focus:ring-teal-600/20">Create SEO record</a>
            @endif
            <x-admin.status-badge status="Readiness" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="SEO Growth Center summary">
        <x-seo.metric-card label="Targets" :value="$summary['target_count']" description="Language and target pairs" />
        <x-seo.metric-card label="Records" :value="$summary['record_count']" description="Prepared metadata records" />
        <x-seo.metric-card label="Coverage" :value="$summary['coverage'].'%'" description="Record foundation coverage" />
        <x-seo.metric-card label="Missing metadata" :value="$summary['missing_metadata']" description="Title or description gaps" />
        <x-seo.metric-card label="Sitemap" :value="$summary['sitemap']" description="Included records" />
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <main class="min-w-0 overflow-hidden space-y-6">
            <x-seo.filter-bar :filters="$filters" :locales="$locales" :target-types="$targetTypes" />

            <section aria-labelledby="seo-records-title" class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="seo-records-title" class="text-lg font-extrabold text-stone-950">SEO records</h2>
                        <p class="mt-1 text-sm text-stone-600">Records store metadata only. Page, blog, and section content remains owned by its source module.</p>
                    </div>
                    <p class="text-sm font-bold text-stone-500">{{ $records->total() }} records</p>
                </div>

                @if ($records->count() > 0)
                    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed text-left text-sm">
                                <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                    <tr>
                                        <th scope="col" class="w-52 px-4 py-3">Target</th>
                                        <th scope="col" class="w-64 px-4 py-3">Metadata</th>
                                        <th scope="col" class="w-28 px-4 py-3">Robots</th>
                                        <th scope="col" class="w-32 px-4 py-3">Sitemap</th>
                                        <th scope="col" class="w-32 px-4 py-3">Schema</th>
                                        <th scope="col" class="w-56 px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $record)
                                        <x-seo.target-row :record="$record" :target-types="$targetTypes" :can-update="$canUpdateSeo" />
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <x-admin.pagination :paginator="$records" />
                @else
                    <x-seo.empty-state />
                @endif
            </section>

            <section aria-labelledby="seo-target-queue-title" class="space-y-4">
                <div>
                    <h2 id="seo-target-queue-title" class="text-lg font-extrabold text-stone-950">Target coverage queue</h2>
                    <p class="mt-1 text-sm text-stone-600">Prepare SEO records for system routes and existing content without moving content into SEO.</p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($targetQueue as $target)
                        <x-seo.target-card :target="$target" :can-update="$canUpdateSeo" />
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="min-w-0 space-y-6">
            <x-seo.health-summary :summary="$summary" />

            <x-admin.card title="Boundary rules" description="SEO Growth Center stores discovery metadata only.">
                <div class="space-y-3 text-sm text-stone-700">
                    <p>Page Studio keeps page content.</p>
                    <p>Blog Studio keeps post, category, and tag content.</p>
                    <p>Sections Studio keeps homepage and trust blocks.</p>
                    <p>Media Library will own OG image selection in a later editor step.</p>
                </div>
            </x-admin.card>

            <x-admin.card title="Settings readiness" description="Reserved for later SEO settings work.">
                <div class="space-y-3 text-sm text-stone-700">
                    <p>Sitemap generation is intentionally deferred.</p>
                    <p>Robots.txt editor is intentionally deferred.</p>
                    <p>Duplicate detection and Search Console integration are intentionally deferred.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
