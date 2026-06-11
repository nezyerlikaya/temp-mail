@props(['name', 'label', 'value', 'languages'])

@php($invalid = $errors->has($name))
<div>
    <label for="{{ $name }}" class="block text-sm font-bold text-stone-900">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $invalid ? 'true' : 'false' }}" aria-describedby="{{ $invalid ? $name.'-error' : '' }}">
        @foreach ($languages as $code => $language)
            <option value="{{ $code }}" @selected(old($name, $value) === $code)>{{ $language }} ({{ $code }})</option>
        @endforeach
    </select>
    @error($name)<p id="{{ $name }}-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
</div>
