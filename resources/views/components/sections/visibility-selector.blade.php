@props(['visibilities', 'selected' => 'all'])

<fieldset>
    <legend class="text-sm font-extrabold text-stone-800">Device visibility</legend>
    <div class="mt-2 grid grid-cols-3 gap-2">
        @foreach ($visibilities as $value => $label)
            <label class="flex min-h-11 cursor-pointer items-center justify-center rounded-lg border border-stone-300 px-2 text-center text-xs font-extrabold text-stone-700 has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-800 focus-within:ring-4 focus-within:ring-teal-600/20">
                <input type="radio" name="device_visibility" value="{{ $value }}" class="sr-only" @checked($selected === $value) x-on:change="dirty = true">
                {{ $label }}
            </label>
        @endforeach
    </div>
</fieldset>
