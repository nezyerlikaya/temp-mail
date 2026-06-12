@props(['url', 'enabled' => true, 'label' => 'Preview'])

@if ($enabled)
    <a href="{{ $url }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20']) }}>
        {{ $label }}
    </a>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-200 bg-stone-50 px-3 py-2 text-sm font-extrabold text-stone-400']) }} aria-disabled="true">
        {{ $label }}
    </span>
@endif
