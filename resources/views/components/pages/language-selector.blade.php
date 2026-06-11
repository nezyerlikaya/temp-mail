@props(['locales', 'selected' => null])

<div>
    <label for="page-locale" class="text-sm font-extrabold text-stone-950">Language <span class="text-red-700">*</span></label>
    <select
        id="page-locale"
        name="locale_id"
        class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('locale_id') aria-invalid="true" aria-describedby="page-locale-error" @enderror
        required
    >
        <option value="">Choose language</option>
        @foreach ($locales as $locale)
            <option value="{{ $locale->id }}" @selected((string) $selected === (string) $locale->id)>{{ $locale->language_name }} ({{ $locale->locale }})</option>
        @endforeach
    </select>
    @error('locale_id')
        <p id="page-locale-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
