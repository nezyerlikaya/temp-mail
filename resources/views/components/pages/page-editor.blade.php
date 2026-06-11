@props(['page' => null, 'editor', 'action', 'method' => 'POST'])

@php
    $selectedLocale = old('locale_id', $page?->locale_id);
    $selectedType = old('page_type', $page?->page_type ?? 'contact');
    $selectedStatus = old('status', $page?->status ?? 'draft');
    $selectedReadiness = old('content_readiness', $page?->content_readiness ?? 'outline');
    $title = old('title', $page?->title ?? '');
    $slug = old('slug', $page?->slug ?? '');
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
    x-data="{
        submitting: false,
        dirty: false,
        intent: 'save_draft',
        title: @js($title),
        slug: @js($slug),
        slugify(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-{2,}/g, '-');
        },
        generateSlug() {
            this.slug = this.slugify(this.title);
            this.dirty = true;
        }
    }"
    x-on:input="dirty = true"
    x-on:change="dirty = true"
    x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; dirty = false"
    x-on:beforeunload.window="if (dirty && ! submitting) { $event.preventDefault(); $event.returnValue = ''; }"
    x-bind:aria-busy="submitting"
    x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <main class="min-w-0 space-y-6">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-stone-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Page editor</p>
                    <h2 class="mt-1 text-xl font-extrabold text-stone-950">Content workspace</h2>
                    <p class="mt-2 max-w-2xl text-sm text-stone-600">Pages are independent language-specific records. Keep copy, slug, and media aligned before publishing.</p>
                </div>
                <div x-cloak x-show="dirty" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-extrabold text-amber-900" role="status">
                    Unsaved changes
                </div>
            </div>

            <div class="mt-5 space-y-5">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-pages.language-selector :locales="$editor['locales']" :selected="$selectedLocale" />
                    <x-pages.page-type-selector :types="$editor['pageTypes']" :selected="$selectedType" />
                </div>

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div>
                        <label for="page-title" class="text-sm font-extrabold text-stone-950">Title <span class="text-red-700">*</span></label>
                        <input
                            id="page-title"
                            name="title"
                            x-model="title"
                            value="{{ $title }}"
                            autocomplete="off"
                            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                            @error('title') aria-invalid="true" aria-describedby="page-title-error" @enderror
                            required
                        >
                        @error('title')
                            <p id="page-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-pages.slug-field :value="$slug" />
                </div>

                <div>
                    <label for="page-excerpt" class="text-sm font-extrabold text-stone-950">Excerpt / summary</label>
                    <textarea
                        id="page-excerpt"
                        name="excerpt"
                        rows="3"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('excerpt') aria-invalid="true" aria-describedby="page-excerpt-error" @enderror
                    >{{ old('excerpt', $page?->excerpt) }}</textarea>
                    @error('excerpt')
                        <p id="page-excerpt-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <label for="page-content" class="text-sm font-extrabold text-stone-950">Content editor</label>
                        <div class="flex gap-2 text-xs font-bold text-stone-500" aria-hidden="true">
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">H2</span>
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">List</span>
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">Quote</span>
                        </div>
                    </div>
                    <textarea
                        id="page-content"
                        name="content"
                        rows="16"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-4 py-3 text-sm leading-7 text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        placeholder="Write the page content..."
                        @error('content') aria-invalid="true" aria-describedby="page-content-error" @else aria-describedby="page-content-help" @enderror
                    >{{ old('content', $page?->content) }}</textarea>
                    @error('content')
                        <p id="page-content-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @else
                        <p id="page-content-help" class="mt-2 text-xs font-bold text-stone-500">Plain content storage for Page Studio. SEO and public rendering arrive in later parts.</p>
                    @enderror
                </div>

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                    <x-pages.featured-media-field
                        :selected="$editor['selectedMedia']"
                        :assets="$editor['mediaAssets']"
                        :media-library-ready="$editor['mediaLibraryReady']"
                        :can-select="$editor['canSelectMedia']"
                        :can-upload="$editor['canUploadMedia']"
                        :fallback-value="$page?->featured_media_id"
                    />

                    <div>
                        <label for="page-readiness" class="text-sm font-extrabold text-stone-950">Content readiness</label>
                        <select id="page-readiness" name="content_readiness" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                            @foreach ($editor['readinessOptions'] as $value => $label)
                                <option value="{{ $value }}" @selected($selectedReadiness === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-pages.publish-panel :page="$page" :statuses="$editor['statuses']" :selected-status="$selectedStatus" :can-publish="$editor['canPublish']" :can-hide="$editor['canHide']" />
</form>
