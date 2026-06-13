<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Temp Mail') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-stone-900 antialiased" style="{{ $publicTypographyStyle ?? '' }}">
        @include('themes.legacy.partials.header')
        <main>
            @yield('content')
        </main>
        @include('themes.legacy.partials.footer')
    </body>
</html>
