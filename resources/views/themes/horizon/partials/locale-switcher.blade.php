@if (count($navigation['locale_switcher']) > 1)
    <details class="relative">
        <summary class="cursor-pointer list-none border border-stone-200 bg-white px-3 py-2 text-stone-700 focus:outline-none focus:ring-4 focus:ring-emerald-600/25">{{ strtoupper($locale['code']) }}</summary>
        <div class="absolute end-0 top-full z-20 mt-2 min-w-44 border border-stone-200 bg-white p-2 shadow-xl">
            @foreach ($navigation['locale_switcher'] as $option)
                <a href="{{ $option['url'] }}" lang="{{ $option['code'] }}" dir="{{ $option['direction'] }}" @class(['block px-3 py-2 text-sm hover:bg-stone-100 focus:bg-stone-100 focus:outline-none', 'font-extrabold text-emerald-800' => $option['current']]) @if ($option['current']) aria-current="page" @endif>{{ $option['label'] }}</a>
            @endforeach
        </div>
    </details>
@endif
