@props(['name', 'label', 'value', 'default'])

@php($id = 'appearance-'.$name)
@php($errorKey = 'tokens.'.$name)

<div class="rounded-md border border-stone-200 bg-white p-4">
    <div class="flex items-start justify-between gap-3">
        <label for="{{ $id }}" class="text-sm font-extrabold text-stone-950">{{ $label }}</label>
        <button type="submit" form="reset-token-{{ $name }}" class="text-xs font-extrabold text-stone-500 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Reset</button>
    </div>
    <div class="mt-3 flex items-center gap-3">
        <input
            id="{{ $id }}"
            name="tokens[{{ $name }}]"
            type="color"
            value="{{ old($errorKey, $value) }}"
            class="size-11 shrink-0 rounded-md border border-stone-300 bg-white p-1 focus:outline-none focus:ring-4 focus:ring-teal-700/20"
            aria-describedby="{{ $id }}-help @error($errorKey) {{ $id }}-error @enderror"
            aria-invalid="@error($errorKey) true @else false @enderror"
            x-on:input="dirty = true; window.dispatchEvent(new CustomEvent('appearance-token-changed', { detail: { name: '{{ $name }}', value: $event.target.value } }))"
            required
        >
        <input
            type="text"
            value="{{ old($errorKey, $value) }}"
            pattern="^#[0-9a-fA-F]{6}$"
            class="min-w-0 flex-1 rounded-md border border-stone-300 px-3 py-2 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/15"
            x-on:input="dirty = true; document.getElementById('{{ $id }}').value = $event.target.value; window.dispatchEvent(new CustomEvent('appearance-token-changed', { detail: { name: '{{ $name }}', value: $event.target.value } }))"
            aria-label="{{ $label }} hex value"
        >
    </div>
    <p id="{{ $id }}-help" class="mt-2 text-xs font-semibold text-stone-500">Default {{ $default }}. Safe #RRGGBB values only.</p>
    @error($errorKey)
        <p id="{{ $id }}-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</div>
