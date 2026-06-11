@props(['value', 'timezones'])

@php($invalid = $errors->has('default_timezone'))
<div>
    <label for="default_timezone" class="block text-sm font-bold text-stone-900">Default timezone</label>
    <select id="default_timezone" name="default_timezone" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $invalid ? 'true' : 'false' }}" aria-describedby="{{ $invalid ? 'default-timezone-error' : '' }}">
        @foreach ($timezones as $timezone)
            <option value="{{ $timezone }}" @selected(old('default_timezone', $value) === $timezone)>{{ $timezone }}</option>
        @endforeach
    </select>
    @error('default_timezone')<p id="default-timezone-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
</div>
