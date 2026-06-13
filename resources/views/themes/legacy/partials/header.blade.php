@php
    $brand = $brand ?? ['name' => config('app.name', 'Temp Mail Cloud'), 'logo' => null];
    $navigation = $navigation ?? ['primary' => [['label' => 'Home', 'url' => route('home'), 'current' => true]], 'locale_switcher' => []];
    $locale = $locale ?? ['code' => config('app.locale', 'en')];
@endphp

<header class="border-b-2 border-stone-950 bg-white">
    <div class="mx-auto flex min-h-18 max-w-5xl items-center justify-between gap-4 px-4">
        <a href="{{ $navigation['primary'][0]['url'] }}" class="flex min-w-0 items-center gap-3 font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-yellow-300">
            @if ($brand['logo'])
                <img src="{{ $brand['logo']['url'] }}" alt="{{ $brand['name'] }}" class="h-8 w-auto max-w-44 object-contain">
            @else
                <span class="truncate">{{ $brand['name'] }}</span>
            @endif
        </a>
        <nav class="flex items-center gap-2 text-sm font-bold text-stone-700" aria-label="Primary navigation">
            @foreach ($navigation['primary'] as $item)
                <a href="{{ $item['url'] }}" class="px-3 py-2 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-yellow-300" @if ($item['current']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            @include('themes.legacy.partials.locale-switcher')
        </nav>
    </div>
</header>
