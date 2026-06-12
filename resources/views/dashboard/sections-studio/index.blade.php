<x-admin.layout title="Sections Studio" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Sections Studio"
        description="Language-specific CTA, FAQ, trust, and teaser content records for future theme rendering."
    >
        <x-slot:actions>
            @if ($canCreateSection)
                <a href="{{ route('admin.sections-studio.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm focus:outline-none focus:ring-4 focus:ring-teal-600/20">Create section</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Sections Studio summary">
            @foreach ([
                'Sections' => $summary['total'] ?? 0,
                'Draft' => $summary['draft'] ?? 0,
                'Active' => $summary['active'] ?? 0,
                'Hidden' => $summary['hidden'] ?? 0,
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
                <x-sections.filter-bar :filters="$filters" :locales="$locales" :types="$types" :placements="$placements" :statuses="$statuses" />

                @if ($canReorderSection && ($filters['locale_id'] ?? 'all') !== 'all' && ($filters['placement'] ?? 'all') !== 'all' && $sections->count() > 1)
                    @php
                        $orderItems = $sections->map(fn ($section): array => ['id' => $section->id, 'title' => $section->title])->values();
                    @endphp
                    <form method="POST" action="{{ route('admin.sections-studio.reorder') }}" class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm" x-data="{
                        items: {{ Illuminate\Support\Js::from($orderItems) }},
                        draggedId: null,
                        moveBefore(targetId) {
                            if (!this.draggedId || this.draggedId === targetId) return;
                            const from = this.items.findIndex(item => item.id === this.draggedId);
                            const to = this.items.findIndex(item => item.id === targetId);
                            const [moved] = this.items.splice(from, 1);
                            this.items.splice(to, 0, moved);
                        }
                    }">
                        @csrf
                        <input type="hidden" name="locale_id" value="{{ $filters['locale_id'] }}">
                        <input type="hidden" name="placement" value="{{ $filters['placement'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="font-extrabold text-stone-950">Section ordering</h2>
                                <p class="mt-1 text-sm text-stone-600">Drag records within this language and placement scope.</p>
                            </div>
                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Save order</button>
                        </div>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <template x-for="item in items" :key="item.id">
                                <div draggable="true" x-on:dragstart="draggedId = item.id" x-on:dragover.prevent x-on:drop.prevent="moveBefore(item.id)" class="flex items-center gap-3 rounded-lg border border-stone-200 bg-stone-50 p-3">
                                    <input type="hidden" name="order[]" x-bind:value="item.id">
                                    <x-sections.drag-handle />
                                    <span class="min-w-0 truncate text-sm font-extrabold text-stone-800" x-text="item.title"></span>
                                </div>
                            </template>
                        </div>
                    </form>
                @endif

                @if ($sections->count() > 0)
                    <section class="space-y-6" aria-labelledby="section-results-title">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <h2 id="section-results-title" class="text-lg font-extrabold text-stone-950">Section records</h2>
                                <p class="mt-1 text-sm text-stone-600">Filter by language, type, placement, or status. Header and footer are intentionally not editable here.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $sections->total() }} records</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($sections as $section)
                                <x-sections.section-card :section="$section" :types="$types" :placements="$placements" />
                            @endforeach
                        </div>

                        <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Section</th>
                                            <th scope="col" class="px-4 py-3">Language</th>
                                            <th scope="col" class="px-4 py-3">Type</th>
                                            <th scope="col" class="px-4 py-3">Placement</th>
                                            <th scope="col" class="px-4 py-3">Status</th>
                                            <th scope="col" class="px-4 py-3">Visibility</th>
                                            <th scope="col" class="px-4 py-3">Items</th>
                                            <th scope="col" class="px-4 py-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sections as $section)
                                            <x-sections.section-row :section="$section" :types="$types" :placements="$placements" />
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{ $sections->links() }}
                    </section>
                @else
                    <x-sections.empty-state />
                @endif
            </main>

            <aside class="min-w-0 space-y-6">
                <x-admin.card title="Foundation rules" description="Sections remain language-specific records.">
                    <div class="space-y-3 text-sm">
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Theme-owned chrome</p>
                            <p class="mt-1 text-stone-600">Header and Footer are not editable section types.</p>
                        </div>
                        <div class="rounded-lg border border-stone-200 p-3">
                            <p class="font-extrabold text-stone-950">Rendering later</p>
                            <p class="mt-1 text-stone-600">Public theme placement and drag/drop ordering are intentionally deferred.</p>
                        </div>
                    </div>
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin.layout>
