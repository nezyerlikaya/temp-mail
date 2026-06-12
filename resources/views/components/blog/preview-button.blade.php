@props(['url' => null, 'enabled' => true, 'label' => 'Preview'])

@if ($enabled && $url)
    <a href="{{ $url }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20']) }}>
        {{ $label }}
    </a>
@else
    <button type="button" disabled {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-200 bg-stone-100 px-3 py-2 text-sm font-extrabold text-stone-400']) }}>
        {{ $label }}
    </button>
@endif
