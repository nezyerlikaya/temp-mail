@extends('themes.horizon.layouts.public')

@section('content')
    <section class="overflow-hidden border-b border-stone-200 bg-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <p class="mb-5 inline-flex items-center gap-2 text-sm font-bold text-emerald-800">
                    <span class="size-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    {{ $brand['tagline'] ?: $brand['name'] }}
                </p>
                <h1 class="text-4xl font-extrabold leading-tight text-stone-950 sm:text-5xl lg:text-6xl">{{ $translations['home.hero.title'] }}</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-stone-600">{{ $translations['home.hero.description'] }}</p>
                <div class="mt-9 flex flex-wrap gap-3 text-sm font-bold text-stone-700">
                    <span class="border border-stone-200 bg-[#f4f7f6] px-4 py-2">{{ $translations['home.badge.no_permanent_inbox'] }}</span>
                    <span class="border border-stone-200 bg-[#f4f7f6] px-4 py-2">{{ $translations['home.badge.privacy_first'] }}</span>
                    <span class="border border-stone-200 bg-[#f4f7f6] px-4 py-2">{{ $locale['native_name'] }}</span>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-xl" aria-hidden="true">
                <div class="border border-stone-200 bg-[#102b28] p-4 shadow-[18px_18px_0_#d9ede7] sm:p-6">
                    <div class="flex items-center justify-between border-b border-white/15 pb-4">
                        <span class="text-sm font-extrabold text-white">inbox@private.test</span>
                        <span class="bg-emerald-300 px-2 py-1 text-xs font-extrabold text-emerald-950">{{ strtoupper($translations['home.visual.ready']) }}</span>
                    </div>
                    <div class="space-y-3 pt-5">
                        <div class="border border-white/15 bg-white/10 p-4">
                            <div class="mb-3 h-2 w-24 bg-rose-300/80"></div>
                            <div class="h-2 w-3/4 bg-white/75"></div>
                            <div class="mt-2 h-2 w-1/2 bg-white/30"></div>
                        </div>
                        <div class="border border-white/15 bg-white/5 p-4">
                            <div class="mb-3 h-2 w-16 bg-emerald-300/80"></div>
                            <div class="h-2 w-2/3 bg-white/60"></div>
                            <div class="mt-2 h-2 w-2/5 bg-white/25"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
