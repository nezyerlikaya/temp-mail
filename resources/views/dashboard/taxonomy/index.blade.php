<x-admin.layout title="Taxonomy" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Taxonomy"
        description="Manage language-specific categories and tags for Blog Studio."
    >
        <x-slot:actions>
            <a href="{{ route('admin.blog-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-extrabold text-stone-800 shadow-sm transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                Blog Studio
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-blog.validation-summary />

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Taxonomy summary">
            @foreach ([
                'Categories' => $summary['categories'] ?? 0,
                'Active categories' => $summary['active_categories'] ?? 0,
                'Tags' => $summary['tags'] ?? 0,
                'Active tags' => $summary['active_tags'] ?? 0,
            ] as $label => $value)
                <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        <x-blog.taxonomy-tabs :active="$filters['tab']" />

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <main class="min-w-0 space-y-6">
                <x-blog.taxonomy-filter-bar :filters="$filters" :locales="$locales" :statuses="$statuses" />

                @if (($filters['tab'] ?? 'categories') === 'categories')
                    <section class="space-y-4" aria-labelledby="category-results-title">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <h2 id="category-results-title" class="text-lg font-extrabold text-stone-950">Categories</h2>
                                <p class="mt-1 text-sm text-stone-600">Editorial groups are scoped to one language and counted by attached posts.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $categories->total() }} records</p>
                        </div>

                        @if ($categories->count() > 0)
                            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-sm">
                                        <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                            <tr>
                                                <th scope="col" class="px-4 py-3">Category</th>
                                                <th scope="col" class="px-4 py-3">Language</th>
                                                <th scope="col" class="px-4 py-3">Status</th>
                                                <th scope="col" class="px-4 py-3">Posts</th>
                                                <th scope="col" class="px-4 py-3">Order</th>
                                                <th scope="col" class="px-4 py-3 text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categories as $category)
                                                <x-blog.category-row :category="$category" :can-update="$canUpdateTaxonomy" />
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{ $categories->links() }}
                        @else
                            <x-blog.taxonomy-empty-state type="categories" />
                        @endif
                    </section>
                @else
                    <section class="space-y-4" aria-labelledby="tag-results-title">
                        <div class="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <h2 id="tag-results-title" class="text-lg font-extrabold text-stone-950">Tags</h2>
                                <p class="mt-1 text-sm text-stone-600">Topic labels stay language-specific and can be attached only to same-language posts.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $tags->total() }} records</p>
                        </div>

                        @if ($tags->count() > 0)
                            <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-left text-sm">
                                        <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                            <tr>
                                                <th scope="col" class="px-4 py-3">Tag</th>
                                                <th scope="col" class="px-4 py-3">Language</th>
                                                <th scope="col" class="px-4 py-3">Status</th>
                                                <th scope="col" class="px-4 py-3">Posts</th>
                                                <th scope="col" class="px-4 py-3 text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tags as $tag)
                                                <x-blog.tag-row :tag="$tag" :can-update="$canUpdateTaxonomy" />
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{ $tags->links() }}
                        @else
                            <x-blog.taxonomy-empty-state type="tags" />
                        @endif
                    </section>
                @endif
            </main>

            <aside class="min-w-0 space-y-6">
                <x-admin.card
                    :title="($filters['tab'] ?? 'categories') === 'categories' ? ($editingCategory ? 'Edit category' : 'Create category') : ($editingTag ? 'Edit tag' : 'Create tag')"
                    description="Taxonomy records are created per language, not translated in place."
                >
                    @if (! $canCreateTaxonomy && ! $canUpdateTaxonomy)
                        <p class="text-sm font-bold text-stone-600">Your role can view taxonomy records but cannot change them.</p>
                    @elseif (($filters['tab'] ?? 'categories') === 'categories')
                        @if ($editingCategory && $canUpdateTaxonomy)
                            <x-blog.category-editor
                                :category="$editingCategory"
                                :locales="$locales"
                                :statuses="$statuses"
                                :action="route('admin.taxonomy.categories.update', ['blogCategory' => $editingCategory, 'tab' => 'categories', 'edit_category' => $editingCategory->id])"
                                method="PUT"
                            />
                        @elseif ($canCreateTaxonomy)
                            <x-blog.category-editor
                                :locales="$locales"
                                :statuses="$statuses"
                                :action="route('admin.taxonomy.categories.store', ['tab' => 'categories'])"
                            />
                        @endif
                    @else
                        @if ($editingTag && $canUpdateTaxonomy)
                            <x-blog.tag-editor
                                :tag="$editingTag"
                                :locales="$locales"
                                :statuses="$statuses"
                                :action="route('admin.taxonomy.tags.update', ['blogTag' => $editingTag, 'tab' => 'tags', 'edit_tag' => $editingTag->id])"
                                method="PUT"
                            />
                        @elseif ($canCreateTaxonomy)
                            <x-blog.tag-editor
                                :locales="$locales"
                                :statuses="$statuses"
                                :action="route('admin.taxonomy.tags.store', ['tab' => 'tags'])"
                            />
                        @endif
                    @endif
                </x-admin.card>

                <x-admin.card title="Language guardrails" description="Blog taxonomy deliberately avoids translation tables.">
                    <div class="space-y-3 text-sm">
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Same-language attach</p>
                            <p class="mt-1 text-stone-600">Posts can only receive categories and tags from their own locale.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Trash readiness</p>
                            <p class="mt-1 text-stone-600">Use the hidden or trashed statuses to prepare lifecycle flows without deleting records.</p>
                        </div>
                    </div>
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin.layout>
