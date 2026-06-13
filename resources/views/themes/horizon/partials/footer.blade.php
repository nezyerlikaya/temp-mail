@php
    $brand = $brand ?? ['footer_text' => config('app.name', 'Temp Mail Cloud')];
    $translations = $translations ?? ['footer.copyright' => 'All rights reserved.'];
    $current_year = $current_year ?? date('Y');
@endphp

<footer class="bg-[#102b28] text-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p class="font-bold">{{ $brand['footer_text'] }}</p>
        <p class="text-white/65">&copy; {{ $current_year }} {{ $translations['footer.copyright'] }}</p>
    </div>
</footer>
