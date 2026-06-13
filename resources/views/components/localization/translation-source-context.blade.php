@props(['source'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-stone-50 p-4']) }}>
    <div class="flex flex-wrap items-center gap-2">
        <x-localization.translation-key-badge :key-name="$source->translation_key" />
        <x-localization.translation-status-badge :status="$source->is_required ? 'required' : 'optional'" />
    </div>
    <p class="mt-3 text-xs font-extrabold uppercase text-stone-500">English canonical source</p>
    <p class="mt-1 text-sm font-bold leading-6 text-stone-950">{{ $source->source_value }}</p>
    @if ($source->description)
        <p class="mt-2 text-xs leading-5 text-stone-600">{{ $source->description }}</p>
    @endif
</div>
