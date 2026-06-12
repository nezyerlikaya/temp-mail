@props(['locales', 'selected' => null])

<div>
    <label for="seo-language" class="text-sm font-extrabold text-stone-950">Language</label>
    <select id="seo-language" name="locale_id" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('locale_id') aria-invalid="true" aria-describedby="seo-language-error" @enderror>
        <option value="">Choose language</option>
        @foreach ($locales as $locale)
            <option value="{{ $locale->id }}" @selected((string) old('locale_id', $selected) === (string) $locale->id)>{{ $locale->language_name }} · {{ $locale->locale }}</option>
        @endforeach
    </select>
    @error('locale_id')
        <p id="seo-language-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
