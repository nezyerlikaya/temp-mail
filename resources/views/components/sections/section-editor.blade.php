@props(['section' => null, 'editor', 'action', 'method' => 'POST'])

@php
    $selectedType = old('section_type', $section?->section_type ?? 'cta');
    $selectedStatus = old('status', $section?->status ?? 'draft');
    $settings = $section?->settings ?? [];
@endphp

<div
    x-data="{ dirty: false, submitting: false, sectionType: @js($selectedType), status: @js($selectedStatus) }"
    x-on:beforeunload.window="if (dirty && ! submitting) { $event.preventDefault(); $event.returnValue = ''; }"
    class="space-y-6"
>
    <div x-cloak x-show="dirty && ! submitting" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-bold text-amber-900" role="status">
        You have unsaved changes.
    </div>

    <form method="POST" action="{{ $action }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]" x-on:input="dirty = true" x-on:change="dirty = true" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; dirty = false" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <main class="min-w-0 space-y-6">
            <x-admin.card title="Section content" description="Each section is an independent language-specific record.">
                <div class="space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="section-locale" class="text-sm font-extrabold text-stone-800">Language</label>
                            <select id="section-locale" name="locale_id" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('locale_id') aria-invalid="true" aria-describedby="section-locale-error" @enderror>
                                <option value="">Choose language</option>
                                @foreach ($editor['locales'] as $locale)
                                    <option value="{{ $locale->id }}" @selected((string) old('locale_id', $section?->locale_id) === (string) $locale->id)>{{ $locale->language_name }}</option>
                                @endforeach
                            </select>
                            @error('locale_id') <p id="section-locale-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <x-sections.section-type-selector :types="$editor['types']" :selected="$selectedType" />
                    </div>

                    <x-sections.placement-selector :placements="$editor['placements']" :selected="old('placement', $section?->placement ?? 'home.primary')" />

                    <div>
                        <label for="section-title" class="text-sm font-extrabold text-stone-800">Title</label>
                        <input id="section-title" name="title" value="{{ old('title', $section?->title) }}" type="text" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('title') aria-invalid="true" aria-describedby="section-title-error" @enderror>
                        @error('title') <p id="section-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="section-subtitle" class="text-sm font-extrabold text-stone-800">Subtitle or description</label>
                        <textarea id="section-subtitle" name="subtitle" rows="3" class="mt-2 block w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('subtitle', $section?->subtitle) }}</textarea>
                    </div>

                    <div>
                        <label for="section-content" class="text-sm font-extrabold text-stone-800">Content</label>
                        <textarea id="section-content" name="content" rows="8" class="mt-2 block w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('content', $section?->content) }}</textarea>
                    </div>

                    <x-sections.cta-editor :settings="$settings" />
                    <x-sections.blog-teaser-editor :settings="$settings" :categories="$editor['blogCategories']" :locale-id="$section?->locale_id" />
                </div>
            </x-admin.card>
        </main>

        <aside class="min-w-0 space-y-6">
            <x-admin.card title="Publishing state" description="Active/passive readiness without public rendering.">
                <div class="space-y-5">
                    <div>
                        <label for="section-status" class="text-sm font-extrabold text-stone-800">Status</label>
                        <select id="section-status" name="status" x-model="status" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                            @foreach ($editor['statuses'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex min-h-11 cursor-pointer items-center justify-between rounded-lg border border-stone-200 px-3">
                        <span class="text-sm font-extrabold text-stone-800">Section active</span>
                        <input type="checkbox" x-bind:checked="status === 'active'" x-on:change="status = $event.target.checked ? 'active' : 'hidden'; dirty = true" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
                    </label>

                    <x-sections.visibility-selector :visibilities="$editor['deviceVisibilities']" :selected="old('device_visibility', $section?->device_visibility ?? 'all')" />

                    <div>
                        <label for="section-visibility" class="text-sm font-extrabold text-stone-800">Audience visibility</label>
                        <select id="section-visibility" name="visibility" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                            @foreach ($editor['visibilities'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('visibility', $section?->visibility ?? 'public') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="section-sort-order" class="text-sm font-extrabold text-stone-800">Sort order</label>
                        <input id="section-sort-order" name="sort_order" value="{{ old('sort_order', $section?->sort_order ?? 0) }}" type="number" min="0" max="9999" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    </div>

                    <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                        <span x-text="submitting ? 'Saving...' : '{{ $section ? 'Update section' : 'Create section' }}'"></span>
                    </button>
                </div>
            </x-admin.card>
        </aside>
    </form>

    @if ($section && $section->section_type === 'faq')
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]" aria-labelledby="faq-items-title">
            <div class="min-w-0">
                <h2 id="faq-items-title" class="text-lg font-extrabold text-stone-950">FAQ accordion items</h2>
                <p class="mt-1 text-sm text-stone-600">Preview, update, soft-remove, and reorder questions inside this language-specific FAQ.</p>
                <div class="mt-4">
                    <x-sections.item-list :section="$section" :items="$editor['items']" :quality="$editor['faqQuality']" :can-reorder="$editor['canReorder']" />
                </div>
            </div>
            <aside class="min-w-0">
                @if ($editor['canUpdateItems'])
                    <x-sections.faq-item-editor :section="$section" :statuses="$editor['itemStatuses']" />
                @endif
            </aside>
        </section>
    @endif
</div>
