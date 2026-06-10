@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} · Temp Mail Cloud</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f6f5f1] font-sans text-stone-950 antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mx-auto mb-8 flex w-max items-center gap-3 rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-bold shadow-sm focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                    <span class="grid size-8 place-items-center rounded-full bg-teal-700 text-white">TM</span>
                    Temp Mail Cloud
                </a>

                <section class="rounded-lg border border-stone-200 bg-white p-6 shadow-xl shadow-stone-200/70 sm:p-8">
                    {{ $slot }}
                </section>
            </div>
        </main>
    </body>
</html>
