@props(['asset', 'readiness'])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white shadow-sm']) }} aria-labelledby="avatar-readiness-title">
    <header class="border-b border-stone-200 px-5 py-4">
        <h2 id="avatar-readiness-title" class="text-base font-extrabold text-stone-950">Avatar readiness</h2>
        <p class="mt-1 text-sm text-stone-600">Prepared for People &amp; Identity integration.</p>
    </header>

    <div class="space-y-4 p-5">
        <div class="flex items-center gap-4">
            <div class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-full bg-teal-700 text-xl font-extrabold text-white ring-4 ring-stone-100">
                @if ($readiness['url'])
                    <img src="{{ $readiness['url'] }}" alt="" class="size-full object-cover">
                @else
                    {{ str($asset->title ?: $asset->original_name)->substr(0, 2)->upper() }}
                @endif
            </div>
            <div>
                <p class="text-sm font-extrabold text-stone-950">{{ $readiness['square'] ? 'Square crop ready' : 'Square crop recommended' }}</p>
                <p class="mt-1 text-sm text-stone-600">{{ $readiness['recommendation'] }}</p>
            </div>
        </div>

        <div class="grid gap-2 text-sm sm:grid-cols-2">
            <div class="rounded-lg bg-stone-50 p-3"><span class="font-extrabold text-stone-950">Initials fallback</span><span class="mt-1 block text-stone-600">Ready</span></div>
            <div class="rounded-lg bg-stone-50 p-3"><span class="font-extrabold text-stone-950">Color fallback</span><span class="mt-1 block text-stone-600">Ready</span></div>
        </div>

        @if ($readiness['crop_hook'])
            <p class="text-xs font-bold text-stone-500">Future crop hook available. Interactive cropping is intentionally deferred.</p>
        @endif
    </div>
</section>
