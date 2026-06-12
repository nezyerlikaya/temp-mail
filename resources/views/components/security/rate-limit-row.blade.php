@props(['policy', 'strategies', 'canUpdate' => false])

@php
    $base = 'policies.'.$policy['key'].'.';
@endphp

<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <input type="hidden" name="policies[{{ $policy['key'] }}][key]" value="{{ $policy['key'] }}">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $policy['label'] }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">{{ $policy['key'] }}</p>
        </div>
        <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
            <input type="hidden" name="policies[{{ $policy['key'] }}][is_active]" value="0">
            <input type="checkbox" name="policies[{{ $policy['key'] }}][is_active]" value="1" @checked(old($base.'is_active', $policy['is_active'])) @disabled(! $canUpdate) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
            <span>Active</span>
        </label>
    </div>

    <div class="mt-4 grid gap-3 md:grid-cols-4">
        @foreach ([['max_attempts', 'Maximum attempts'], ['window_minutes', 'Window minutes'], ['cooldown_minutes', 'Cooldown minutes']] as [$field, $label])
            <label class="grid gap-2">
                <span class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</span>
                <input
                    type="number"
                    min="1"
                    name="policies[{{ $policy['key'] }}][{{ $field }}]"
                    value="{{ old($base.$field, $policy[$field]) }}"
                    @disabled(! $canUpdate)
                    aria-invalid="{{ $errors->has($base.$field) ? 'true' : 'false' }}"
                    aria-describedby="{{ $errors->has($base.$field) ? $policy['key'].'-'.$field.'-error' : '' }}"
                    class="no-spinner min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"
                >
                @error($base.$field)
                    <span id="{{ $policy['key'] }}-{{ $field }}-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
                @enderror
            </label>
        @endforeach

        <label class="grid gap-2">
            <span class="text-xs font-extrabold uppercase text-stone-500">Strategy</span>
            <select name="policies[{{ $policy['key'] }}][strategy]" @disabled(! $canUpdate) class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
                @foreach ($strategies as $value => $label)
                    <option value="{{ $value }}" @selected(old($base.'strategy', $policy['strategy']) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
</div>
