@props(['link'])

@if ($link)
    <a href="{{ $link['url'] }}" class="inline-flex min-h-10 items-center justify-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        {{ $link['label'] }}
    </a>
@else
    <span class="inline-flex min-h-10 items-center rounded-md border border-stone-200 px-4 text-sm font-bold text-stone-500">
        Action unavailable
    </span>
@endif
