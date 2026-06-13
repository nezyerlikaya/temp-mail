@props(['usage', 'label', 'families', 'assignment' => null, 'warnings' => [], 'disabled' => false])

@php
    $selected = old('assignments.'.$usage.'.font_family_slug', $assignment?->font_family_slug);
    $fallback = old('assignments.'.$usage.'.fallback_stack', $assignment?->fallback_stack ?? []);
    $fieldId = 'font-'.$usage.'-'.uniqid();
    $errorKey = 'assignments.'.$usage.'.font_family_slug';
@endphp

<fieldset class="rounded-md border border-stone-200 p-4">
    <legend class="px-1 text-sm font-extrabold text-stone-950">{{ $label }}</legend>
    <label for="{{ $fieldId }}" class="mt-2 grid gap-2 text-sm font-bold text-stone-700">
        <span>Primary font</span>
        <select id="{{ $fieldId }}" name="assignments[{{ $usage }}][font_family_slug]" @disabled($disabled) aria-invalid="{{ $errors->has($errorKey) ? 'true' : 'false' }}" aria-describedby="{{ $errors->has($errorKey) ? $fieldId.'-error' : $fieldId.'-hint' }}" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:bg-stone-100 disabled:text-stone-500">
            @foreach ($families as $family)
                <option value="{{ $family->slug }}" @selected($selected === $family->slug)>{{ $family->name }}</option>
            @endforeach
        </select>
    </label>

    @error($errorKey)
        <p id="{{ $fieldId }}-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p id="{{ $fieldId }}-hint" class="mt-2 text-xs font-semibold text-stone-500">Fallback entries are selected from the safe registry only.</p>
    @enderror

    <div class="mt-3 grid gap-2">
        <span class="text-xs font-extrabold uppercase text-stone-500">Fallback stack</span>
        @for ($index = 0; $index < 3; $index++)
            <select name="assignments[{{ $usage }}][fallback_stack][]" @disabled($disabled) class="min-h-10 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:bg-stone-100 disabled:text-stone-500">
                <option value="">Use default fallback</option>
                @foreach ($families as $family)
                    <option value="{{ $family->slug }}" @selected(($fallback[$index] ?? null) === $family->slug)>{{ $family->name }}</option>
                @endforeach
                @foreach (['sans-serif', 'serif', 'monospace', 'ui-sans-serif', 'ui-monospace', 'Arial', 'Helvetica', 'Tahoma'] as $safeFallback)
                    <option value="{{ $safeFallback }}" @selected(($fallback[$index] ?? null) === $safeFallback)>{{ $safeFallback }}</option>
                @endforeach
            </select>
        @endfor
    </div>

    @if ($selected && ! empty($warnings[$selected]))
        <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-950" role="status">
            @foreach ($warnings[$selected] as $warning)
                <p>{{ $warning['message'] }}</p>
            @endforeach
        </div>
    @endif
</fieldset>
