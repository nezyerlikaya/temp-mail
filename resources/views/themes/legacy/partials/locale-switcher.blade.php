@if (count($navigation['locale_switcher']) > 1)
    <details class="relative">
        <summary class="cursor-pointer list-none border border-stone-950 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-yellow-300">{{ strtoupper($locale['code']) }}</summary>
        <div class="absolute end-0 top-full z-20 mt-2 min-w-44 border-2 border-stone-950 bg-white p-1">
            @foreach ($navigation['locale_switcher'] as $option)
                <a href="{{ $option['url'] }}" lang="{{ $option['code'] }}" dir="{{ $option['direction'] }}" @class(['block px-3 py-2 hover:bg-yellow-100 focus:bg-yellow-100 focus:outline-none', 'font-extrabold underline' => $option['current']]) @if ($option['current']) aria-current="page" @endif>{{ $option['label'] }}</a>
            @endforeach
        </div>
    </details>
@endif
