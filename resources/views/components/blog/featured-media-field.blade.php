@props([
    'selected' => null,
    'assets' => [],
    'mediaLibraryReady' => false,
    'canSelect' => false,
    'canUpload' => false,
    'fallbackValue' => null,
])

<div>
    @if ($mediaLibraryReady)
        <x-media.picker
            name="featured_media_id"
            label="Featured image"
            :selected="$selected"
            :assets="$assets"
            type="image"
            :can-select="$canSelect"
            :can-upload="$canUpload"
        />
    @else
        <label for="blog-featured-media-id" class="text-sm font-extrabold text-stone-950">Featured media</label>
        <input
            id="blog-featured-media-id"
            name="featured_media_id"
            value="{{ old('featured_media_id', $fallbackValue) }}"
            inputmode="numeric"
            autocomplete="off"
            placeholder="Media asset ID"
            class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            @error('featured_media_id') aria-invalid="true" aria-describedby="blog-featured-media-id-error" @else aria-describedby="blog-featured-media-id-help" @enderror
        >
        @error('featured_media_id')
            <p id="blog-featured-media-id-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
        @else
            <p id="blog-featured-media-id-help" class="mt-2 text-xs font-bold text-stone-500">Media Library is not available yet, so this accepts an existing media asset ID only.</p>
        @enderror
    @endif
</div>
