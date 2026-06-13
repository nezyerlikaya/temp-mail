<x-admin.layout title="Translation Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Markets"
        title="Translation Center"
        description="Manage predefined product interface source keys. English is the canonical source language."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Canonical English" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-localization.translation-validation-summary />

    <section class="mb-6 flex flex-col gap-4 rounded-lg border border-stone-200 bg-white p-4 shadow-sm lg:flex-row lg:items-end lg:justify-between" aria-label="Translation Center workspace">
        <div>
            <p class="text-xs font-extrabold uppercase text-stone-500">Workspace mode</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <a href="{{ route('admin.translation-center.index') }}" @class(['rounded-lg px-4 py-2 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20', 'bg-stone-950 text-white' => ! $isEditor, 'border border-stone-300 text-stone-700' => $isEditor])>
                    Source registry
                </a>
                @if ($targetLocales->isNotEmpty())
                    <a href="{{ route('admin.translation-center.index', ['mode' => 'editor', 'locale' => $selectedLocale?->locale ?? $targetLocales->first()->locale]) }}" @class(['rounded-lg px-4 py-2 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20', 'bg-stone-950 text-white' => $isEditor, 'border border-stone-300 text-stone-700' => ! $isEditor])>
                        Locale editor
                    </a>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('admin.translation-center.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
            <input type="hidden" name="mode" value="editor">
            <div>
                <label for="target-locale" class="text-xs font-extrabold uppercase text-stone-500">Active target locale</label>
                <select id="target-locale" name="locale" class="mt-1 min-h-11 min-w-56 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled($targetLocales->isEmpty())>
                    @forelse ($targetLocales as $locale)
                        <option value="{{ $locale->locale }}" @selected($selectedLocale?->is($locale))>{{ $locale->language_name }} ({{ $locale->locale }})</option>
                    @empty
                        <option>No active target locales</option>
                    @endforelse
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25" @disabled($targetLocales->isEmpty())>
                Open editor
            </button>
        </form>
    </section>

    @if ($isEditor)
        <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Translation coverage">
            @foreach ([
                ['label' => 'Locale coverage', 'value' => $coverage['coverage'].'%', 'description' => $coverage['completed'].' of '.$coverage['total'].' keys completed'],
                ['label' => 'Required coverage', 'value' => $coverage['required_coverage'].'%', 'description' => $coverage['required_completed'].' of '.$coverage['required_total'].' required keys'],
                ['label' => 'Missing queue', 'value' => $coverage['missing'], 'description' => 'Keys falling back to English'],
                ['label' => 'Publish readiness', 'value' => $coverage['publish_ready'] ? 'Ready' : 'Needs review', 'description' => $coverage['published'].' published, '.$coverage['reviewed'].' reviewed'],
            ] as $metric)
                <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-extrabold uppercase text-stone-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $metric['value'] }}</p>
                    <p class="mt-1 text-sm text-stone-600">{{ $metric['description'] }}</p>
                </div>
            @endforeach
        </section>

        <div class="space-y-5">
            <x-localization.translation-group-tabs :groups="$groups" :filters="$filters" :total="$coverage['total']" />
            <x-localization.translation-filters :filters="$filters" :groups="$groups" editor />

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
                <form
                    method="POST"
                    action="{{ route('admin.translation-center.translations.save') }}"
                    class="min-w-0 space-y-4"
                    x-data="{
                        dirty: false,
                        submitting: false,
                        selectAll: false,
                        selectedCount: 0,
                        toggleAll() {
                            document.querySelectorAll('.js-translation-select').forEach((box) => box.checked = this.selectAll);
                            this.updateSelected();
                        },
                        updateSelected() {
                            this.selectedCount = document.querySelectorAll('.js-translation-select:checked').length;
                        }
                    }"
                    x-on:change="dirty = true; updateSelected()"
                    x-on:submit="submitting = true; dirty = false; $el.classList.add('pointer-events-none', 'opacity-75'); $el.setAttribute('aria-busy', 'true')"
                    x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = '' }"
                >
                    @csrf
                    <input type="hidden" name="locale" value="{{ $selectedLocale->locale }}">
                    <div class="sr-only" role="status" aria-live="polite" x-text="dirty ? 'Unsaved translation changes.' : 'Translation editor ready.'"></div>

                    <x-localization.translation-bulk-actions :can-review="$canReviewTranslations" :can-publish="$canPublishTranslations" />

                    @if ($sources->count() > 0)
                        @foreach ($sources as $source)
                            <x-localization.translation-editor-row :source="$source" :locale="$selectedLocale" :can-edit="$canEditTranslations" />
                        @endforeach

                        <x-admin.pagination :paginator="$sources" />
                        <x-localization.translation-save-bar :can-edit="$canEditTranslations" />
                    @else
                        <x-localization.translation-empty-state />
                    @endif
                </form>

                <aside class="space-y-5">
                    <x-admin.card title="Group coverage" description="Progress is calculated from active source keys.">
                        <div class="space-y-4">
                            @foreach ($groupCoverage as $group)
                                <x-localization.translation-progress :score="$group['coverage']" />
                                <p class="-mt-3 text-xs font-bold text-stone-600">{{ $group['label'] }}: {{ $group['completed'] }}/{{ $group['total'] }}</p>
                            @endforeach
                        </div>
                    </x-admin.card>

                    <x-admin.card title="Publishing safety" description="English remains the canonical fallback.">
                        <div class="space-y-3 text-sm leading-6 text-stone-700">
                            <p>Saving changed copy returns it to Draft.</p>
                            <p>Only saved values can be reviewed.</p>
                            <p>Only reviewed values can be published.</p>
                            <p>Missing or unpublished values safely resolve to English.</p>
                        </div>
                    </x-admin.card>
                </aside>
            </div>
        </div>
    @else

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Translation source summary">
        @foreach ([
            ['label' => 'Source keys', 'value' => $summary['total'], 'description' => 'Registered UI keys'],
            ['label' => 'Active keys', 'value' => $summary['active'], 'description' => 'Available for translation'],
            ['label' => 'Required keys', 'value' => $summary['required'], 'description' => 'Core product copy'],
            ['label' => 'Missing readiness', 'value' => $summary['missing'], 'description' => 'Non-English locale slots'],
        ] as $metric)
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-wide text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-2 text-3xl font-extrabold text-stone-950">{{ $metric['value'] }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $metric['description'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="space-y-5">
        <x-localization.translation-group-tabs :groups="$groups" :filters="$filters" :total="$summary['total']" />
        <x-localization.translation-filters :filters="$filters" :groups="$groups" />

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="min-w-0 space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-stone-950">Source key registry</h2>
                        <p class="mt-1 text-sm text-stone-600">Search and classify public UI copy before locale editing begins.</p>
                    </div>
                    <p class="text-sm font-bold text-stone-500">{{ $sources->total() }} matching keys</p>
                </div>

                @if ($sources->count() > 0)
                    <div class="space-y-4">
                        @foreach ($sources as $source)
                            <x-localization.translation-source-row :source="$source" :groups="$groups" :can-manage="$canManageSources" />
                        @endforeach
                    </div>

                    <x-admin.pagination :paginator="$sources" />
                @else
                    <x-localization.translation-empty-state />
                @endif
            </div>

            <aside class="space-y-5">
                @if ($canManageSources)
                    <x-admin.card title="Create source key" description="Add controlled UI keys only. Content records stay in their own studios.">
                        <form
                            method="POST"
                            action="{{ route('admin.translation-center.sources.store') }}"
                            class="space-y-4"
                            x-data
                            x-on:submit="$el.classList.add('pointer-events-none', 'opacity-70'); $el.setAttribute('aria-busy', 'true'); $el.querySelector('button[type=submit]').disabled = true"
                        >
                            @csrf

                            <div>
                                <label for="group_key" class="text-sm font-extrabold text-stone-800">Group</label>
                                <select id="group_key" name="group_key" aria-invalid="@error('group_key') true @else false @enderror" aria-describedby="@error('group_key') group_key-error @enderror" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                    @foreach ($groups as $key => $label)
                                        <option value="{{ $key }}" @selected(old('group_key', $filters['group'] !== 'all' ? $filters['group'] : 'common') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('group_key')
                                    <p id="group_key-error" class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="translation_key" class="text-sm font-extrabold text-stone-800">Translation key</label>
                                <input id="translation_key" name="translation_key" value="{{ old('translation_key') }}" autocomplete="off" spellcheck="false" placeholder="home.hero.subtitle" aria-invalid="@error('translation_key') true @else false @enderror" aria-describedby="@error('translation_key') translation_key-error @else translation_key-help @enderror" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 font-mono text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                @error('translation_key')
                                    <p id="translation_key-error" class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>
                                @else
                                    <p id="translation_key-help" class="mt-1 text-xs font-semibold text-stone-500">Use lowercase dot notation, for example home.hero.subtitle.</p>
                                @enderror
                            </div>

                            <div>
                                <label for="source_value" class="text-sm font-extrabold text-stone-800">English source value</label>
                                <textarea id="source_value" name="source_value" rows="4" aria-invalid="@error('source_value') true @else false @enderror" aria-describedby="@error('source_value') source_value-error @enderror" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('source_value') }}</textarea>
                                @error('source_value')
                                    <p id="source_value-error" class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="text-sm font-extrabold text-stone-800">Description/context</label>
                                <textarea id="description" name="description" rows="3" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('description') }}</textarea>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="value_type" class="text-sm font-extrabold text-stone-800">Value type</label>
                                    <select id="value_type" name="value_type" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                        <option value="short_text" @selected(old('value_type') === 'short_text')>Short text</option>
                                        <option value="long_text" @selected(old('value_type') === 'long_text')>Long text</option>
                                        <option value="rich_text" @selected(old('value_type') === 'rich_text')>Rich text readiness</option>
                                        <option value="boolean" @selected(old('value_type') === 'boolean')>Boolean/readiness</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="sort_order" class="text-sm font-extrabold text-stone-800">Sort order</label>
                                    <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', 100) }}" class="mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                </div>
                            </div>

                            <div class="space-y-3">
                                <input type="hidden" name="is_required" value="0">
                                <label class="inline-flex items-center gap-2 text-sm font-bold text-stone-700">
                                    <input type="checkbox" name="is_required" value="1" @checked(old('is_required', true)) class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                                    Required source key
                                </label>
                                <input type="hidden" name="is_active" value="0">
                                <label class="inline-flex items-center gap-2 text-sm font-bold text-stone-700">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                                    Active
                                </label>
                            </div>

                            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60">
                                Create source key
                            </button>
                        </form>
                    </x-admin.card>
                @endif

                <x-admin.card title="Translation boundary" description="This registry is intentionally narrow.">
                    <div class="space-y-3 text-sm leading-6 text-stone-700">
                        <p>English source values are canonical.</p>
                        <p>Blog posts, pages, sections, SEO records, and email templates stay as independent content records.</p>
                        <p>Locale editing, coverage, import/export, and runtime switching are deferred to later Translation Center steps.</p>
                    </div>
                </x-admin.card>
            </aside>
        </div>
    </div>
    @endif
</x-admin.layout>
