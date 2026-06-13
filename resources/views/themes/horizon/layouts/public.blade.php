<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale['code']) }}" dir="{{ $locale['direction'] }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('themes.horizon.partials.seo-meta')
        @if (! empty($faq_schema))
            <script type="application/ld+json">{!! json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif
        @if ($brand['favicon'])
            <link rel="icon" href="{{ $brand['favicon']['url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f4f7f6] font-sans text-stone-950 antialiased" style="{{ $style }}" data-public-theme="{{ $theme['slug'] }}">
        <a href="#main-content" class="sr-only z-50 bg-white px-4 py-3 font-bold text-stone-950 focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:outline-none focus:ring-4 focus:ring-emerald-600/30">Skip to content</a>
        @include('themes.horizon.partials.header')
        <main id="main-content" tabindex="-1">@yield('content')</main>
        @include('themes.horizon.partials.footer')
    </body>
</html>
