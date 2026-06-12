@props(['tags' => collect(), 'selected' => []])

<fieldset>
    <legend class="text-sm font-extrabold text-stone-950">Tags</legend>
    <div class="mt-2 max-h-44 overflow-y-auto rounded-lg border border-stone-300 bg-white p-2" @error('tag_ids') aria-invalid="true" aria-describedby="blog-tag-ids-error" @else aria-describedby="blog-tag-ids-help" @enderror>
        @forelse ($tags as $tag)
            <label class="flex min-h-9 items-center gap-2 rounded-md px-2 py-1.5 text-sm font-bold text-stone-700 hover:bg-stone-50">
                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20" @checked(in_array((string) $tag->id, array_map('strval', old('tag_ids', $selected)), true))>
                <span class="min-w-0 truncate">{{ $tag->name }} <span class="text-xs text-stone-500">({{ $tag->locale?->locale ?? 'unknown' }})</span></span>
            </label>
        @empty
            <p class="px-2 py-3 text-sm font-bold text-stone-500">No tags yet.</p>
        @endforelse
    </div>
    @error('tag_ids')
        <p id="blog-tag-ids-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p id="blog-tag-ids-help" class="mt-2 text-xs font-bold text-stone-500">Only tags from the selected language can be saved.</p>
    @enderror
    @error('tag_ids.*')
        <p class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</fieldset>
