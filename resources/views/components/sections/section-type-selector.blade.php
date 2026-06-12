@props(['types', 'selected' => 'cta'])

<div>
    <label for="section-type" class="text-sm font-extrabold text-stone-800">Section type</label>
    <select id="section-type" name="section_type" x-model="sectionType" x-on:change="dirty = true" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('section_type') aria-invalid="true" aria-describedby="section-type-error" @enderror>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('section_type') <p id="section-type-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
</div>
