@extends('themes.atlas.layouts.public')

@section('content')
    <section id="mailbox-creator" class="border-b border-white/10">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8 lg:py-12">
            <div class="flex flex-col justify-center">
                <p class="font-mono text-sm font-bold uppercase text-lime-300">PUBLIC / {{ strtoupper($locale['code']) }} / {{ strtoupper($translations['home.visual.ready']) }}</p>
                <h1 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight text-white sm:text-4xl">{{ $translations['home.hero.title'] }}</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-stone-300">{{ $translations['home.hero.description'] }}</p>
            </div>
            @include('themes.atlas.partials.mailbox-creator')
        </div>
    </section>

    @include('themes.atlas.partials.cta')
    @include('themes.atlas.partials.blog-teaser')
    @include('themes.atlas.partials.faq')
@endsection
