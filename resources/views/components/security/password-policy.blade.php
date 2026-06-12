@props(['settings', 'readiness', 'canUpdate' => false])

<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold text-stone-950">Password policy readiness</p>
            <p class="mt-1 text-sm leading-6 text-stone-600">Client hints mirror the saved policy; server validation remains the source of truth.</p>
        </div>
        <x-security.status-badge :status="$readiness['password_policy']" />
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <label class="grid gap-2">
            <span class="text-xs font-extrabold uppercase text-stone-500">Minimum length</span>
            <input type="number" min="8" max="128" name="password_min_length" value="{{ old('password_min_length', $settings['password_min_length']) }}" @disabled(! $canUpdate) class="no-spinner min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
            @error('password_min_length')
                <span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <div class="grid gap-2">
            <span class="text-xs font-extrabold uppercase text-stone-500">Required character groups</span>
            <div class="flex flex-wrap gap-2">
                @foreach ([['password_letters', 'Letters'], ['password_numbers', 'Numbers'], ['password_symbols', 'Symbols']] as [$field, $label])
                    <label class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-200 bg-white px-3 text-sm font-bold text-stone-700">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field])) @disabled(! $canUpdate) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
