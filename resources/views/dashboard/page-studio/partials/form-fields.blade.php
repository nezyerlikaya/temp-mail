@php
    $selectedLocale = old('locale_id', $page?->locale_id);
    $selectedType = old('page_type', $page?->page_type ?? 'contact');
    $selectedStatus = old('status', $page?->status ?? 'draft');
    $selectedReadiness = old('content_readiness', $page?->content_readiness ?? 'outline');
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    <div>
        <label for="page-locale" class="text-sm font-extrabold text-stone-950">Language <span class="text-red-700">*</span></label>
        <select
            id="page-locale"
            name="locale_id"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            @error('locale_id') aria-invalid="true" aria-describedby="page-locale-error" @enderror
            required
        >
            <option value="">Choose language</option>
            @foreach ($locales as $locale)
                <option value="{{ $locale->id }}" @selected((string) $selectedLocale === (string) $locale->id)>{{ $locale->language_name }} ({{ $locale->locale }})</option>
            @endforeach
        </select>
        @error('locale_id')
            <p id="page-locale-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="page-type" class="text-sm font-extrabold text-stone-950">Page type <span class="text-red-700">*</span></label>
        <select id="page-type" name="page_type" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" required>
            @foreach ($pageTypes as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
    <div>
        <label for="page-title" class="text-sm font-extrabold text-stone-950">Title <span class="text-red-700">*</span></label>
        <input
            id="page-title"
            name="title"
            value="{{ old('title', $page?->title) }}"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            @error('title') aria-invalid="true" aria-describedby="page-title-error" @enderror
            required
        >
        @error('title')
            <p id="page-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="page-slug" class="text-sm font-extrabold text-stone-950">Slug</label>
        <input
            id="page-slug"
            name="slug"
            value="{{ old('slug', $page?->slug) }}"
            inputmode="url"
            autocomplete="off"
            placeholder="auto-from-title"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            @error('slug') aria-invalid="true" aria-describedby="page-slug-error" @enderror
        >
        @error('slug')
            <p id="page-slug-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @else
            <p class="mt-2 text-xs font-bold text-stone-500">Lowercase letters, numbers, and hyphens only. Unique per language.</p>
        @enderror
    </div>
</div>

<div>
    <label for="page-excerpt" class="text-sm font-extrabold text-stone-950">Excerpt / summary</label>
    <textarea
        id="page-excerpt"
        name="excerpt"
        rows="3"
        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('excerpt') aria-invalid="true" aria-describedby="page-excerpt-error" @enderror
    >{{ old('excerpt', $page?->excerpt) }}</textarea>
    @error('excerpt')
        <p id="page-excerpt-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <div>
        <label for="page-readiness" class="text-sm font-extrabold text-stone-950">Content readiness</label>
        <select id="page-readiness" name="content_readiness" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            @foreach ($readinessOptions as $value => $label)
                <option value="{{ $value }}" @selected($selectedReadiness === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="page-status" class="text-sm font-extrabold text-stone-950">Status</label>
        <select id="page-status" name="status" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="page-published-at" class="text-sm font-extrabold text-stone-950">Published at readiness</label>
        <input
            id="page-published-at"
            name="published_at"
            type="datetime-local"
            value="{{ old('published_at', $page?->published_at?->format('Y-m-d\TH:i')) }}"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        >
    </div>
</div>

<div>
    <label for="page-featured-media-id" class="text-sm font-extrabold text-stone-950">Featured media reference readiness</label>
    <input
        id="page-featured-media-id"
        name="featured_media_id"
        value="{{ old('featured_media_id', $page?->featured_media_id) }}"
        inputmode="numeric"
        placeholder="Media asset ID"
        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('featured_media_id') aria-invalid="true" aria-describedby="page-featured-media-id-error" @enderror
    >
    @error('featured_media_id')
        <p id="page-featured-media-id-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p class="mt-2 text-xs font-bold text-stone-500">Temporary media hook only. Full picker integration is handled by later Page Studio work.</p>
    @enderror
</div>
