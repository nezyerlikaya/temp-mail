@extends('themes.atlas.layouts.public')

@section('content')
    <section class="border-b border-white/10">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1fr_.9fr] lg:px-8 lg:py-24">
            <div>
                <p class="mb-5 font-mono text-sm font-bold uppercase text-lime-300">PUBLIC / {{ strtoupper($locale['code']) }} / {{ strtoupper($translations['home.visual.ready']) }}</p>
                <h1 class="max-w-3xl text-4xl font-extrabold leading-tight text-white sm:text-5xl lg:text-6xl">{{ $translations['home.hero.title'] }}</h1>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-300">{{ $translations['home.hero.description'] }}</p>
                <dl class="mt-10 grid max-w-xl grid-cols-2 border border-white/15 text-sm">
                    <div class="border-e border-white/15 p-4"><dt class="text-stone-400">Locale</dt><dd class="mt-1 font-extrabold text-cyan-300">{{ $locale['native_name'] }}</dd></div>
                    <div class="p-4"><dt class="text-stone-400">Direction</dt><dd class="mt-1 font-extrabold text-lime-300">{{ strtoupper($locale['direction']) }}</dd></div>
                </dl>
            </div>
            <div class="self-center border border-white/15 bg-[#1c2421] p-5 shadow-[12px_12px_0_#b9f227]">
                <div class="mb-5 flex items-center justify-between font-mono text-xs text-stone-400">
                    <span>{{ strtoupper($translations['home.visual.mailbox_stream']) }}</span><span class="text-lime-300">{{ strtoupper($translations['home.visual.ready']) }}</span>
                </div>
                <div class="space-y-3 font-mono text-sm">
                    <div class="border-s-4 border-cyan-300 bg-white/5 p-4"><span class="text-cyan-300">01</span> &nbsp; verification received</div>
                    <div class="border-s-4 border-lime-300 bg-white/5 p-4"><span class="text-lime-300">02</span> &nbsp; inbox isolated</div>
                    <div class="border-s-4 border-fuchsia-300 bg-white/5 p-4"><span class="text-fuchsia-300">03</span> &nbsp; identity protected</div>
                </div>
            </div>
        </div>
    </section>
@endsection
