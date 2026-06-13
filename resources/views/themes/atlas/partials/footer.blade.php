@php
    $brand = $brand ?? ['footer_text' => config('app.name', 'Temp Mail Cloud')];
    $translations = $translations ?? ['footer.copyright' => 'All rights reserved.'];
    $current_year = $current_year ?? date('Y');
@endphp

<footer class="bg-[#0b0f0e]">
    <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 font-mono text-xs text-stone-400 sm:flex-row sm:justify-between sm:px-6 lg:px-8">
        <p class="text-white">{{ $brand['footer_text'] }}</p>
        <p>&copy; {{ $current_year }} {{ $translations['footer.copyright'] }}</p>
    </div>
</footer>
