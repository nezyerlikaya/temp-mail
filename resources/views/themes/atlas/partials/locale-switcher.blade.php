@if (count($navigation['locale_switcher']) > 1)
    <details class="relative">
        <summary class="cursor-pointer list-none border border-white/20 px-3 py-2 font-mono text-lime-300 focus:outline-none focus:ring-4 focus:ring-lime-300/30">{{ strtoupper($locale['code']) }}</summary>
        <div class="absolute end-0 top-full z-20 mt-2 min-w-44 border border-white/15 bg-[#1c2421] p-2 shadow-xl">
            @foreach ($navigation['locale_switcher'] as $option)
                <a href="{{ $option['url'] }}" lang="{{ $option['code'] }}" dir="{{ $option['direction'] }}" @class(['block px-3 py-2 text-sm text-stone-200 hover:bg-white/10 focus:bg-white/10 focus:outline-none', 'font-extrabold text-lime-300' => $option['current']]) @if ($option['current']) aria-current="page" @endif>{{ $option['label'] }}</a>
            @endforeach
        </div>
    </details>
@endif
