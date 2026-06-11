@props(['readiness'])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white shadow-sm']) }} aria-labelledby="seo-readiness-title">
    <header class="border-b border-stone-200 px-5 py-4">
        <h2 id="seo-readiness-title" class="text-base font-extrabold text-stone-950">SEO / OG readiness</h2>
        <p class="mt-1 text-sm text-stone-600">Social preview checks for the future SEO Growth Center.</p>
    </header>

    <div class="space-y-4 p-5">
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-lg bg-stone-50 p-3">
                <dt class="font-bold text-stone-500">Recommended size</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ $readiness['recommended_dimensions'] }}</dd>
            </div>
            <div class="rounded-lg bg-stone-50 p-3">
                <dt class="font-bold text-stone-500">Dimensions</dt>
                <dd class="mt-1 font-extrabold {{ $readiness['dimensions_ready'] ? 'text-emerald-700' : 'text-amber-800' }}">{{ $readiness['dimensions_ready'] ? 'Ready' : 'Review' }}</dd>
            </div>
            <div class="rounded-lg bg-stone-50 p-3">
                <dt class="font-bold text-stone-500">Alt text</dt>
                <dd class="mt-1 font-extrabold {{ $readiness['alt_ready'] ? 'text-emerald-700' : 'text-amber-800' }}">{{ $readiness['alt_ready'] ? 'Ready' : 'Missing' }}</dd>
            </div>
            <div class="rounded-lg bg-stone-50 p-3">
                <dt class="font-bold text-stone-500">File size</dt>
                <dd class="mt-1 font-extrabold {{ $readiness['size_ready'] ? 'text-emerald-700' : 'text-amber-800' }}">{{ $readiness['size_ready'] ? 'Ready' : 'Large' }}</dd>
            </div>
        </dl>

        @if (count($readiness['warnings']) > 0)
            <ul class="space-y-2 text-sm text-amber-900">
                @foreach ($readiness['warnings'] as $warning)
                    <li class="flex gap-2"><i data-lucide="info" class="mt-0.5 size-4 shrink-0" aria-hidden="true"></i><span>{{ $warning }}</span></li>
                @endforeach
            </ul>
        @else
            <p class="text-sm font-bold text-emerald-800">This asset meets the current OG image recommendations.</p>
        @endif
    </div>
</section>
