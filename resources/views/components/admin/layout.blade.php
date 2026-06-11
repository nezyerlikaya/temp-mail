@props(['title', 'user'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} · Temp Mail Cloud</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f4f6f8] font-sans text-stone-950 antialiased" x-data="{ sidebarOpen: false }">
        <a href="#admin-main" class="sr-only fixed left-4 top-4 z-[60] rounded-md bg-white px-4 py-3 text-sm font-bold text-stone-950 shadow-lg focus:not-sr-only focus:outline-none focus:ring-4 focus:ring-teal-600/25">Skip to main content</a>

        <div class="min-h-screen lg:grid lg:grid-cols-[276px_minmax(0,1fr)]">
            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 bg-stone-950/45 lg:hidden" x-on:click="sidebarOpen = false" aria-hidden="true"></div>

            <x-admin.sidebar />

            <div class="min-w-0">
                <x-admin.header :user="$user" />

                <x-admin.main>
                    {{ $slot }}
                </x-admin.main>
            </div>
        </div>
    </body>
</html>
