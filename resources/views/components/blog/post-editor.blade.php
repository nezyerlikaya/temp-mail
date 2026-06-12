@props(['post' => null, 'editor', 'action', 'method' => 'POST'])

@php
    $selectedLocale = old('locale_id', $post?->locale_id);
    $selectedCategory = old('blog_category_id', $post?->blog_category_id);
    $selectedStatus = old('status', $post?->status ?? 'draft');
    $selectedReadiness = old('content_readiness', $post?->content_readiness ?? 'outline');
    $title = old('title', $post?->title ?? '');
    $slug = old('slug', $post?->slug ?? '');
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
                    <p class="text-xs font-bold uppercase text-teal-700">Post editor</p>
                    <h2 class="mt-1 text-xl font-extrabold text-stone-950">Editorial workspace</h2>
                    <p class="mt-2 max-w-2xl text-sm text-stone-600">Write one language-specific post at a time. Categories, tags, and media stay aligned to this record.</p>
                </div>
                <div x-cloak x-show="dirty" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-extrabold text-amber-900" role="status">
                    Unsaved changes
                </div>
            </div>

            <div class="mt-5 space-y-5">
                <x-blog.language-selector :locales="$editor['locales']" :selected="$selectedLocale" />

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div>
                        <label for="blog-title" class="text-sm font-extrabold text-stone-950">Title <span class="text-red-700">*</span></label>
                        <input
                            id="blog-title"
                            name="title"
                            x-model="title"
                            value="{{ $title }}"
                            autocomplete="off"
                            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                            @error('title') aria-invalid="true" aria-describedby="blog-title-error" @enderror
                            required
                        >
                        @error('title')
                            <p id="blog-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-blog.slug-field :value="$slug" />
                </div>

                <div>
                    <label for="blog-excerpt" class="text-sm font-extrabold text-stone-950">Excerpt</label>
                    <textarea
                        id="blog-excerpt"
                        name="excerpt"
                        rows="3"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('excerpt') aria-invalid="true" aria-describedby="blog-excerpt-error" @enderror
                    >{{ old('excerpt', $post?->excerpt) }}</textarea>
                    @error('excerpt')
                        <p id="blog-excerpt-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <label for="blog-content" class="text-sm font-extrabold text-stone-950">Content editor</label>
                        <div class="flex gap-2 text-xs font-bold text-stone-500" aria-hidden="true">
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">H2</span>
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">List</span>
                            <span class="rounded border border-stone-200 bg-stone-50 px-2 py-1">Quote</span>
                        </div>
                    </div>
                    <textarea
                        id="blog-content"
                        name="content"
                        rows="18"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-4 py-3 text-sm leading-7 text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        placeholder="Write the post content..."
                        @error('content') aria-invalid="true" aria-describedby="blog-content-error" @else aria-describedby="blog-content-help" @enderror
                    >{{ old('content', $post?->content) }}</textarea>
                    @error('content')
                        <p id="blog-content-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @else
                        <p id="blog-content-help" class="mt-2 text-xs font-bold text-stone-500">Plain content storage for Blog Studio. SEO, public rendering, and block editing arrive later.</p>
                    @enderror
                </div>

                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                    <x-blog.featured-media-field
                        :selected="$editor['selectedMedia']"
                        :assets="$editor['mediaAssets']"
                        :media-library-ready="$editor['mediaLibraryReady']"
                        :can-select="$editor['canSelectMedia']"
                        :can-upload="$editor['canUploadMedia']"
                        :fallback-value="$post?->featured_media_id"
                    />

                    <div>
                        <label for="blog-readiness" class="text-sm font-extrabold text-stone-950">Content readiness</label>
                        <select id="blog-readiness" name="content_readiness" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                            @foreach ($editor['readinessOptions'] as $value => $label)
                                <option value="{{ $value }}" @selected($selectedReadiness === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-blog.category-picker :categories="$editor['categories']" :selected="$selectedCategory" />
                    <x-blog.tag-picker :tags="$editor['tags']" :selected="$editor['selectedTags']" />
                </div>
            </div>
        </div>
    </main>

    <x-blog.publish-panel :post="$post" :statuses="$editor['statuses']" :selected-status="$selectedStatus" :can-publish="$editor['canPublish']" :can-hide="$editor['canHide']" />
</form>
