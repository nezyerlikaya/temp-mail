<x-admin.layout title="Sections Studio" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Sections Studio"
        description="Language-specific CTA, FAQ, trust, and teaser content records for future theme rendering."
    />

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
                <x-admin.card title="Create foundation" description="A compact first-pass form for section records. Full editor arrives in the next part.">
                    @if ($canCreateSection)
                        <form method="POST" action="{{ route('admin.sections-studio.store') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }">
                            @csrf

                            <div>
                                <label for="section-create-locale" class="text-sm font-extrabold text-stone-800">Language</label>
                                <select id="section-create-locale" name="locale_id" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('locale_id') aria-invalid="true" aria-describedby="section-create-locale-error" @enderror>
                                    <option value="">Choose language</option>
                                    @foreach ($locales as $locale)
                                        <option value="{{ $locale->id }}" @selected((string) old('locale_id') === (string) $locale->id)>{{ $locale->language_name }}</option>
                                    @endforeach
                                </select>
                                @error('locale_id') <p id="section-create-locale-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="section-create-type" class="text-sm font-extrabold text-stone-800">Type</label>
                                    <select id="section-create-type" name="section_type" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                        @foreach ($types as $value => $label)
                                            <option value="{{ $value }}" @selected(old('section_type', 'cta') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="section-create-status" class="text-sm font-extrabold text-stone-800">Status</label>
                                    <select id="section-create-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                        @foreach ($editorStatuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', 'draft') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="section-create-placement" class="text-sm font-extrabold text-stone-800">Placement</label>
                                <select id="section-create-placement" name="placement" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                    @foreach ($placements as $value => $label)
                                        <option value="{{ $value }}" @selected(old('placement', 'home.primary') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="section-create-title" class="text-sm font-extrabold text-stone-800">Title</label>
                                <input id="section-create-title" name="title" value="{{ old('title') }}" type="text" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('title') aria-invalid="true" aria-describedby="section-create-title-error" @enderror>
                                @error('title') <p id="section-create-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                            </div>

                            <input type="hidden" name="visibility" value="{{ old('visibility', 'public') }}">
                            <input type="hidden" name="sort_order" value="{{ old('sort_order', 0) }}">

                            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-70">
                                <span x-text="submitting ? 'Creating...' : 'Create section'"></span>
                            </button>
                        </form>
                    @else
                        <p class="text-sm font-bold text-stone-600">Your role can view sections but cannot create them.</p>
                    @endif
                </x-admin.card>

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
