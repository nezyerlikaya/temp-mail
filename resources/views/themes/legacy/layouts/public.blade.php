<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale['code']) }}" dir="{{ $locale['direction'] }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('themes.legacy.partials.seo-meta')
        @if ($brand['favicon'])
            <link rel="icon" href="{{ $brand['favicon']['url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-stone-900 antialiased" style="{{ $style }}" data-public-theme="{{ $theme['slug'] }}">
        <a href="#main-content" class="sr-only z-50 border-2 border-stone-950 bg-yellow-200 px-4 py-3 font-bold text-stone-950 focus:not-sr-only focus:fixed focus:start-4 focus:top-4">Skip to content</a>
        @include('themes.legacy.partials.header')
        <main id="main-content" tabindex="-1">@yield('content')</main>
        @include('themes.legacy.partials.footer')
    </body>
</html>
