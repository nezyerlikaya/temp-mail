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

    <div class="mb-6">
        <x-seo.health-dashboard :diagnostics="$diagnostics" :can-run="$canRunDiagnostics" />
    </div>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="SEO Growth Center summary">
        <x-seo.metric-card label="Targets" :value="$summary['target_count']" description="Language and target pairs" />
        <x-seo.metric-card label="Records" :value="$summary['record_count']" description="Prepared metadata records" />
        <x-seo.metric-card label="Coverage" :value="$summary['coverage'].'%'" description="Record foundation coverage" />
        <x-seo.metric-card label="Missing metadata" :value="$summary['missing_metadata']" description="Title or description gaps" />
        <x-seo.metric-card label="Sitemap" :value="$summary['sitemap']" description="Included records" />
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <main class="min-w-0 overflow-hidden space-y-6">
            <section aria-labelledby="seo-coverage-title" class="space-y-4">
                <div>
                    <h2 id="seo-coverage-title" class="text-lg font-extrabold text-stone-950">Language coverage</h2>
                    <p class="mt-1 text-sm text-stone-600">Per-language SEO metadata coverage without translation relationships.</p>
                </div>
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($diagnostics['coverage'] as $coverage)
                        <x-seo.coverage-card :coverage="$coverage" />
                    @endforeach
                </div>
            </section>

            <x-seo.issue-queue :issues="$diagnostics['issues']" />

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

            <x-seo.sitemap-status :statuses="$diagnostics['sitemap']" />
            <x-seo.robots-safety-panel :robots="$diagnostics['robots']" />
            <x-seo.hreflang-matrix :hreflang="$diagnostics['hreflang']" />

            <x-seo.template-editor
                :templates="$templates"
                :variables="$templateVariables"
                :target-types="$targetTypes"
                :locales="$locales"
                :can-manage="$canManageTemplates"
            />

            <x-admin.card title="Redirect manager" description="Foundation for 301/302 redirects with loop and source conflict prevention.">
                <form method="POST" action="{{ route('admin.seo-growth-center.redirects.store') }}" class="space-y-3" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting.toString()">
                    @csrf
                    <label class="text-sm font-bold text-stone-700">
                        <span>Source path</span>
                        <input name="source_path" value="{{ old('source_path') }}" placeholder="/old-page" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('source_path') aria-invalid="true" aria-describedby="seo-redirect-source-error" @enderror>
                        @error('source_path') <span id="seo-redirect-source-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-bold text-stone-700">
                        <span>Target URL/path</span>
                        <input name="target_url" value="{{ old('target_url') }}" placeholder="/new-page" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('target_url') aria-invalid="true" aria-describedby="seo-redirect-target-error" @enderror>
                        @error('target_url') <span id="seo-redirect-target-error" class="mt-2 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span> @enderror
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="text-sm font-bold text-stone-700">
                            <span>Status</span>
                            <select name="status_code" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                <option value="301">301</option>
                                <option value="302">302</option>
                            </select>
                        </label>
                        <label class="flex items-end gap-2 text-sm font-bold text-stone-700">
                            <input type="checkbox" name="is_active" value="1" checked class="mb-3 size-4 rounded border-stone-300 text-teal-600 focus:ring-teal-600">
                            <span class="mb-2">Active</span>
                        </label>
                    </div>
                    <button type="submit" @disabled(! $canManageRedirects) x-bind:disabled="submitting || {{ $canManageRedirects ? 'false' : 'true' }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                        <span x-text="submitting ? 'Saving...' : 'Add redirect'"></span>
                    </button>
                </form>
                @if ($redirects->count() > 0)
                    <div class="-mx-4 -mb-4 mt-4 border-t border-stone-100">
                        @foreach ($redirects as $redirect)
                            <x-seo.redirect-row :redirect="$redirect" />
                        @endforeach
                    </div>
                @endif
            </x-admin.card>

            <x-seo.version-history :versions="$versions" :can-rollback="$canRollbackSeo" />

            <x-admin.card title="Boundary rules" description="SEO Growth Center stores discovery metadata only.">
                <div class="space-y-3 text-sm text-stone-700">
                    <p>Page Studio keeps page content.</p>
                    <p>Blog Studio keeps post, category, and tag content.</p>
                    <p>Sections Studio keeps homepage and trust blocks.</p>
                    <p>Media Library owns OG and Twitter image assets.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
