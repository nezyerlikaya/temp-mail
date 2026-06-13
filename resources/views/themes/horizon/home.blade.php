@extends('themes.horizon.layouts.public')

@section('content')
    <section id="mailbox-creator" class="border-b border-stone-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-8 sm:px-6 lg:grid-cols-[.95fr_1.05fr] lg:px-8 lg:py-12">
            <div class="flex flex-col justify-center">
                <p class="text-sm font-extrabold text-emerald-800">{{ $brand['tagline'] ?: $brand['name'] }}</p>
                <h1 class="mt-4 max-w-2xl text-3xl font-extrabold leading-tight text-stone-950 sm:text-4xl">{{ $translations['home.hero.title'] }}</h1>
                <p class="mt-4 max-w-xl text-base leading-7 text-stone-600">{{ $translations['home.hero.description'] }}</p>
                <div class="mt-6 grid max-w-lg grid-cols-3 gap-2 text-xs font-bold text-stone-700">
                    <span class="border border-stone-200 bg-[#f4f7f6] px-3 py-2">{{ $translations['home.badge.no_permanent_inbox'] }}</span>
                    <span class="border border-stone-200 bg-[#f4f7f6] px-3 py-2">{{ $translations['home.badge.privacy_first'] }}</span>
                    <span class="border border-stone-200 bg-[#f4f7f6] px-3 py-2">{{ $locale['native_name'] }}</span>
                </div>
            </div>
            @include('themes.horizon.partials.mailbox-creator')
        </div>
    </section>

    @include('themes.horizon.partials.cta')
    @include('themes.horizon.partials.blog-teaser')
    @include('themes.horizon.partials.faq')
@endsection
