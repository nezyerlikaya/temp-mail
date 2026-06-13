@php
    $brand = $brand ?? ['footer_text' => config('app.name', 'Temp Mail Cloud')];
    $translations = $translations ?? ['footer.copyright' => 'All rights reserved.'];
    $current_year = $current_year ?? date('Y');
@endphp

<footer class="border-t-2 border-stone-950 bg-yellow-100">
    <div class="mx-auto flex max-w-5xl flex-col gap-2 px-4 py-6 text-sm sm:flex-row sm:justify-between">
        <p class="font-extrabold text-stone-950">{{ $brand['footer_text'] }}</p>
        <div class="flex flex-wrap gap-4 text-stone-700">
            @foreach ($legal_links ?? [] as $link)<a href="{{ $link['url'] }}" class="font-bold underline focus:outline-none focus:ring-2 focus:ring-stone-950">{{ $link['label'] }}</a>@endforeach
            <p>&copy; {{ $current_year }} {{ $translations['footer.copyright'] }}</p>
        </div>
    </div>
</footer>
