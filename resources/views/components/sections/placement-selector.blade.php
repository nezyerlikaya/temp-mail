@props(['placements', 'selected' => 'home.primary'])

<div>
    <label for="section-placement" class="text-sm font-extrabold text-stone-800">Placement</label>
    <select id="section-placement" name="placement" x-on:change="dirty = true" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('placement') aria-invalid="true" aria-describedby="section-placement-error" @enderror>
        @foreach ($placements as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('placement') <p id="section-placement-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
</div>
