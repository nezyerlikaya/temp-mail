@extends('themes.legacy.layouts.public')

@section('content')
    <section id="mailbox-creator" class="border-b-2 border-stone-950 bg-white">
        <div class="mx-auto grid max-w-5xl gap-8 px-4 py-8 lg:grid-cols-[.9fr_1.1fr] lg:py-10">
            <div>
                <p class="text-sm font-bold uppercase text-teal-800">{{ $brand['tagline'] ?: $brand['name'] }}</p>
                <h1 class="mt-4 text-3xl font-extrabold leading-tight text-stone-950">{{ $translations['home.hero.title'] }}</h1>
                <p class="mt-4 text-base leading-7 text-stone-600">{{ $translations['home.hero.description'] }}</p>
            </div>
            @include('themes.legacy.partials.mailbox-creator')
        </div>
    </section>

    @include('themes.legacy.partials.cta')
    @include('themes.legacy.partials.blog-teaser')
    @include('themes.legacy.partials.faq')
@endsection
