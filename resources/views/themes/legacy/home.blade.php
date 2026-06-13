@extends('themes.legacy.layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-14 sm:py-20">
        <div class="border-s-8 border-teal-700 ps-6 sm:ps-10">
            <p class="mb-4 text-sm font-bold uppercase text-teal-800">{{ $brand['tagline'] ?: $brand['name'] }}</p>
            <h1 class="max-w-3xl text-4xl font-extrabold leading-tight text-stone-950 sm:text-5xl">{{ $translations['home.hero.title'] }}</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-600">{{ $translations['home.hero.description'] }}</p>
        </div>
        <div class="mt-12 grid gap-px border border-stone-300 bg-stone-300 sm:grid-cols-3" aria-label="Service qualities">
            <div class="bg-white p-5"><strong class="block text-stone-950">{{ $translations['home.feature.simple.title'] }}</strong><span class="mt-1 block text-sm text-stone-600">{{ $translations['home.feature.simple.body'] }}</span></div>
            <div class="bg-white p-5"><strong class="block text-stone-950">{{ $translations['home.feature.private.title'] }}</strong><span class="mt-1 block text-sm text-stone-600">{{ $translations['home.feature.private.body'] }}</span></div>
            <div class="bg-white p-5"><strong class="block text-stone-950">{{ $locale['native_name'] }}</strong><span class="mt-1 block text-sm text-stone-600">{{ strtoupper($locale['direction']) }} {{ $translations['home.feature.locale.body'] }}</span></div>
        </div>
    </section>
@endsection
