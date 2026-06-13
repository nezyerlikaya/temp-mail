<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Temp Mail') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 font-sans text-white antialiased" style="{{ $publicTypographyStyle ?? '' }}">
        @include('themes.atlas.partials.header')
        <main>
            @yield('content')
        </main>
        @include('themes.atlas.partials.footer')
    </body>
</html>
