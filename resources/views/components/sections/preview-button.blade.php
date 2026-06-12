@props(['url' => null, 'enabled' => true])

@if ($enabled && $url)
    <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        Preview
    </a>
@endif
