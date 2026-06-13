<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale['code']) }}" dir="{{ $locale['direction'] }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('themes.atlas.partials.seo-meta')
        @if ($brand['favicon'])
            <link rel="icon" href="{{ $brand['favicon']['url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#121715] font-sans text-white antialiased" style="{{ $style }}" data-public-theme="{{ $theme['slug'] }}">
        <a href="#main-content" class="sr-only z-50 bg-lime-300 px-4 py-3 font-extrabold text-stone-950 focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:outline-none focus:ring-4 focus:ring-lime-300/40">Skip to content</a>
        @include('themes.atlas.partials.header')
        <main id="main-content" tabindex="-1">@yield('content')</main>
        @include('themes.atlas.partials.footer')
    </body>
</html>
