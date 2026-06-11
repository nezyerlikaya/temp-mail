@props(['warnings' => []])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white shadow-sm']) }} aria-labelledby="media-quality-title">
    <header class="border-b border-stone-200 px-5 py-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 id="media-quality-title" class="text-base font-extrabold text-stone-950">Quality checks</h2>
                <p class="mt-1 text-sm text-stone-600">Publishing readiness without changing the original file.</p>
            </div>
            <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold {{ count($warnings) === 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
                {{ count($warnings) === 0 ? 'Ready' : count($warnings).' alerts' }}
            </span>
        </div>
    </header>

    <div class="p-5">
        @if (count($warnings) === 0)
            <p class="text-sm font-bold text-emerald-800">No quality warnings detected.</p>
        @else
            <ul class="space-y-3">
                @foreach ($warnings as $warning)
                    <li class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                        <i data-lucide="triangle-alert" class="mt-0.5 size-4 shrink-0 text-amber-700" aria-hidden="true"></i>
                        <div>
                            <p class="text-sm font-extrabold text-amber-950">{{ $warning['label'] }}</p>
                            <p class="mt-1 text-sm text-amber-900">{{ $warning['message'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
