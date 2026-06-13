@php
    $brand = $brand ?? ['name' => config('app.name', 'Temp Mail Cloud'), 'logo' => null];
    $navigation = $navigation ?? ['primary' => [['label' => 'Home', 'url' => route('home'), 'current' => true]], 'locale_switcher' => []];
    $locale = $locale ?? ['code' => config('app.locale', 'en')];
@endphp

<header class="border-b border-white/10 bg-[#121715]">
    <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ $navigation['primary'][0]['url'] }}" class="flex min-w-0 items-center gap-3 font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-lime-300/30">
            @if ($brand['logo'])
                <img src="{{ $brand['logo']['url'] }}" alt="{{ $brand['name'] }}" class="h-9 w-auto max-w-48 object-contain">
            @else
                <span class="grid size-9 place-items-center border border-lime-300 font-mono text-xs text-lime-300" aria-hidden="true">TM</span>
                <span class="truncate">{{ $brand['name'] }}</span>
            @endif
        </a>
        <nav class="flex items-center gap-3 text-sm font-bold text-stone-300" aria-label="Primary navigation">
            @foreach ($navigation['primary'] as $item)
                <a href="{{ $item['url'] }}" @class(['px-3 py-2 focus:outline-none focus:ring-4 focus:ring-lime-300/30', 'text-lime-300' => $item['current']]) @if ($item['current']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            @include('themes.atlas.partials.locale-switcher')
        </nav>
    </div>
</header>
