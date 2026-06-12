@props(['locales' => collect(), 'selected' => null])

<div>
    <label for="blog-locale-id" class="text-sm font-extrabold text-stone-950">Language <span class="text-red-700">*</span></label>
    <select
        id="blog-locale-id"
        name="locale_id"
        class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('locale_id') aria-invalid="true" aria-describedby="blog-locale-id-error" @else aria-describedby="blog-locale-id-help" @enderror
        required
    >
        <option value="">Choose language</option>
        @foreach ($locales as $locale)
            <option value="{{ $locale->id }}" @selected((string) $selected === (string) $locale->id)>{{ $locale->language_name }} ({{ $locale->locale }})</option>
        @endforeach
    </select>
    @error('locale_id')
        <p id="blog-locale-id-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p id="blog-locale-id-help" class="mt-2 text-xs font-bold text-stone-500">Posts are independent records per language.</p>
    @enderror
</div>
