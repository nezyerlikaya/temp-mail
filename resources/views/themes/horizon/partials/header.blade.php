@php
    $brand = $brand ?? ['name' => config('app.name', 'Temp Mail Cloud'), 'logo' => null];
    $navigation = $navigation ?? ['primary' => [['label' => 'Home', 'url' => route('home'), 'current' => true]], 'locale_switcher' => []];
    $locale = $locale ?? ['code' => config('app.locale', 'en')];
@endphp

<header class="border-b border-stone-200 bg-white">
    <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ $navigation['primary'][0]['url'] }}" class="flex min-w-0 items-center gap-3 font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-600/25">
            @if ($brand['logo'])
                <img src="{{ $brand['logo']['url'] }}" alt="{{ $brand['name'] }}" class="h-9 w-auto max-w-48 object-contain">
            @else
                <span class="grid size-9 shrink-0 place-items-center bg-emerald-700 text-sm text-white" aria-hidden="true">TM</span>
                <span class="truncate">{{ $brand['name'] }}</span>
            @endif
        </a>
        <nav class="flex items-center gap-3 text-sm font-bold text-stone-700" aria-label="Primary navigation">
            @foreach ($navigation['primary'] as $item)
                <a href="{{ $item['url'] }}" @class(['px-3 py-2 focus:outline-none focus:ring-4 focus:ring-emerald-600/25', 'text-emerald-800' => $item['current']]) @if ($item['current']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            @include('themes.horizon.partials.locale-switcher')
        </nav>
    </div>
</header>
