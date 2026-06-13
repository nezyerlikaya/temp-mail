<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('app.locale', 'en')) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Temp Mail') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f6f8fb] font-sans text-stone-950 antialiased" style="{{ $publicTypographyStyle ?? '' }}">
        @include('themes.horizon.partials.header')
        <main>
            @yield('content')
        </main>
        @include('themes.horizon.partials.footer')
    </body>
</html>
