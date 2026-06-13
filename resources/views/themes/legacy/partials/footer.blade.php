@php
    $brand = $brand ?? ['footer_text' => config('app.name', 'Temp Mail Cloud')];
    $translations = $translations ?? ['footer.copyright' => 'All rights reserved.'];
    $current_year = $current_year ?? date('Y');
@endphp

<footer class="border-t-2 border-stone-950 bg-yellow-100">
    <div class="mx-auto flex max-w-5xl flex-col gap-2 px-4 py-6 text-sm sm:flex-row sm:justify-between">
        <p class="font-extrabold text-stone-950">{{ $brand['footer_text'] }}</p>
        <p class="text-stone-700">&copy; {{ $current_year }} {{ $translations['footer.copyright'] }}</p>
    </div>
</footer>
