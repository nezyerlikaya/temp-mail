@if (($posts['pagination']['last_page'] ?? 1) > 1)
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
        @foreach ($posts['pagination']['links'] as $link)
            @if ($link['url'])
                <a href="{{ $link['url'] }}" @class(['grid min-h-11 min-w-11 place-items-center border px-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-emerald-600/25', 'border-emerald-700 bg-emerald-700 text-white' => $link['active'], 'border-stone-200 bg-white text-stone-700' => ! $link['active']]) @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endif
        @endforeach
    </nav>
@endif
