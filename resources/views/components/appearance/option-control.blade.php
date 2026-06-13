@props(['name', 'label', 'value', 'default', 'options'])

@php($errorKey = 'tokens.'.$name)

<fieldset class="rounded-md border border-stone-200 bg-white p-4">
    <div class="flex items-start justify-between gap-3">
        <legend class="text-sm font-extrabold text-stone-950">{{ $label }}</legend>
        <button type="submit" form="reset-token-{{ $name }}" class="text-xs font-extrabold text-stone-500 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Reset</button>
    </div>
    <div class="mt-3 grid gap-2 sm:grid-cols-2">
        @foreach ($options as $option => $cssValue)
            <label class="flex items-center gap-2 rounded-md border border-stone-200 px-3 py-2 text-sm font-bold text-stone-700 transition has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-900">
                <input
                    type="radio"
                    name="tokens[{{ $name }}]"
                    value="{{ $option }}"
                    class="size-4 border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-700/20"
                    @checked(old($errorKey, $value) === $option)
                    x-on:change="dirty = true; window.dispatchEvent(new CustomEvent('appearance-token-changed', { detail: { name: '{{ $name }}', value: $event.target.value } }))"
                    required
                >
                <span>{{ str($option)->headline() }}</span>
            </label>
        @endforeach
    </div>
    <p class="mt-2 text-xs font-semibold text-stone-500">Default {{ str($default)->headline() }}.</p>
    @error($errorKey)
        <p class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</fieldset>
