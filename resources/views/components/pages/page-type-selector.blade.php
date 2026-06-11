@props(['types', 'selected' => null])

<div>
    <label for="page-type" class="text-sm font-extrabold text-stone-950">Page type <span class="text-red-700">*</span></label>
    <select
        id="page-type"
        name="page_type"
        class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-950 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('page_type') aria-invalid="true" aria-describedby="page-type-error" @enderror
        required
    >
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('page_type')
        <p id="page-type-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
