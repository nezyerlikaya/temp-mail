@props(['step' => 1, 'title', 'subtitle' => null])

@php
    $steps = [
        1 => ['label' => 'System Readiness', 'route' => route('install.readiness')],
        2 => ['label' => 'Database Setup', 'route' => route('install.database')],
        3 => ['label' => 'Admin Account', 'route' => route('install.admin')],
        4 => ['label' => 'Finish & Lock', 'route' => route('login')],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }} · Temp Mail Setup</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f6f5f1] font-sans text-[#171717] antialiased">
        <main class="min-h-screen lg:grid lg:grid-cols-[minmax(320px,0.9fr)_minmax(420px,1.1fr)]">
            <aside class="relative overflow-hidden bg-[#111827] px-6 py-8 text-white sm:px-10 lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-12">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(45,212,191,0.22),transparent_28%),linear-gradient(145deg,rgba(17,24,39,1),rgba(31,41,55,1)_58%,rgba(20,83,45,0.72))]"></div>
                <div class="relative">
                    <a href="{{ route('install.readiness') }}" class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/8 px-4 py-2 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-4 focus:ring-teal-200/30">
                        <span class="grid size-8 place-items-center rounded-full bg-teal-300 text-sm font-black text-slate-950">TM</span>
                        Temp Mail Cloud
                    </a>

                    <div class="mt-16 max-w-xl lg:mt-24">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-200">First-run setup</p>
                        <h1 class="mt-4 text-4xl font-bold leading-tight text-white sm:text-5xl">Launch your disposable inbox platform with a clean foundation.</h1>
                        <p class="mt-5 text-base leading-7 text-slate-200">This wizard prepares your environment, verifies your database, creates the first administrator, and seals the installer when everything succeeds.</p>
                    </div>
                </div>

                <div class="relative mt-12 rounded-lg border border-white/12 bg-white/8 p-5 shadow-2xl shadow-black/20 backdrop-blur lg:mt-0">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-white">Install integrity</p>
                            <p class="mt-1 text-sm text-slate-300">Lock file writes only after migrations and admin creation.</p>
                        </div>
                        <span class="rounded-full bg-teal-300/16 px-3 py-1 text-xs font-bold text-teal-100">2026 ready</span>
                    </div>
                </div>
            </aside>

            <section class="flex min-h-screen items-center px-4 py-8 sm:px-8 lg:px-12">
                <div class="mx-auto w-full max-w-2xl">
                    <nav aria-label="Installer progress" class="mb-8">
                        <ol class="grid grid-cols-4 gap-2">
                            @foreach ($steps as $number => $item)
                                <li>
                                    <a href="{{ $item['route'] }}" class="group block rounded-lg border px-3 py-3 transition focus:outline-none focus:ring-4 focus:ring-teal-500/25 {{ $number <= $step ? 'border-teal-700 bg-white shadow-sm' : 'border-stone-200 bg-stone-100 text-stone-500' }}" aria-current="{{ $number === $step ? 'step' : 'false' }}">
                                        <span class="grid size-8 place-items-center rounded-full text-sm font-bold {{ $number <= $step ? 'bg-teal-700 text-white' : 'bg-stone-200 text-stone-600' }}">{{ $number }}</span>
                                        <span class="mt-3 block text-xs font-bold leading-4 sm:text-sm">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>

                    <div class="rounded-lg border border-stone-200 bg-white p-6 shadow-xl shadow-stone-200/70 sm:p-8">
                        <header class="mb-8">
                            <p class="text-sm font-bold uppercase tracking-[0.16em] text-teal-700">Step {{ $step }} of 4</p>
                            <h2 class="mt-2 text-3xl font-bold tracking-normal text-stone-950">{{ $title }}</h2>
                            @if ($subtitle)
                                <p class="mt-3 text-base leading-7 text-stone-600">{{ $subtitle }}</p>
                            @endif
                        </header>

                        {{ $slot }}
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
