@props(['source', 'locale', 'canEdit'])

@php
    $translation = $source->values->first();
    $status = $translation?->status ?? 'missing';
    $fieldName = "translations.{$source->id}.value";
    $fieldId = "translation-{$locale->locale}-{$source->id}";
    $value = old($fieldName, $translation?->value);
@endphp

<article {{ $attributes->merge(['class' => 'grid gap-4 rounded-lg border border-stone-200 bg-white p-4 shadow-sm lg:grid-cols-[32px_minmax(0,0.9fr)_minmax(0,1.1fr)]']) }}>
    <div class="pt-1">
        <input
            type="checkbox"
            name="source_ids[]"
            value="{{ $source->id }}"
            class="js-translation-select h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20"
            aria-label="Select {{ $source->translation_key }}"
        >
    </div>

    <x-localization.translation-source-context :source="$source" />

    <div>
        <div class="flex items-center justify-between gap-3">
            <label for="{{ $fieldId }}" class="text-sm font-extrabold text-stone-900">{{ $locale->language_name }} translation</label>
            <x-localization.translation-status-badge :status="$status" />
        </div>

        @if ($source->value_type === 'boolean')
            <select
                id="{{ $fieldId }}"
                name="translations[{{ $source->id }}][value]"
                class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                aria-invalid="@error($fieldName) true @else false @enderror"
                aria-describedby="@error($fieldName) {{ $fieldId }}-error @enderror"
                @disabled(! $canEdit)
            >
                <option value="">Missing</option>
                <option value="true" @selected($value === 'true')>True</option>
                <option value="false" @selected($value === 'false')>False</option>
            </select>
        @else
            <textarea
                id="{{ $fieldId }}"
                name="translations[{{ $source->id }}][value]"
                rows="{{ $source->value_type === 'short_text' ? 2 : 4 }}"
                dir="{{ $locale->direction }}"
                class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold leading-6 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                aria-invalid="@error($fieldName) true @else false @enderror"
                aria-describedby="@error($fieldName) {{ $fieldId }}-error @enderror"
                @disabled(! $canEdit)
            >{{ $value }}</textarea>
        @endif

        @error($fieldName)
            <p id="{{ $fieldId }}-error" class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>
        @enderror

        @if ($canEdit)
            <div class="mt-3">
                <label for="{{ $fieldId }}-save-status" class="text-xs font-extrabold uppercase text-stone-500">Save status</label>
                <select id="{{ $fieldId }}-save-status" name="translations[{{ $source->id }}][status]" class="mt-1 min-h-10 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="draft" @selected(old("translations.{$source->id}.status", $status) !== 'translated')>Draft</option>
                    <option value="translated" @selected(old("translations.{$source->id}.status", $status) === 'translated')>Translated</option>
                </select>
            </div>
        @endif

        @if (! filled($translation?->value))
            <p class="mt-2 text-xs font-semibold text-amber-800">Runtime fallback: {{ $source->source_value }}</p>
        @endif
    </div>
</article>
