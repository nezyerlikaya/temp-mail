@props(['title' => null, 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white shadow-sm']) }}>
    @if ($title || $description || isset($actions))
        <header class="flex items-start justify-between gap-4 border-b border-stone-200 px-5 py-4 sm:px-6">
            <div>
                @if ($title)
                    <h2 class="text-base font-extrabold text-stone-950">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm leading-5 text-stone-600">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="p-5 sm:p-6">{{ $slot }}</div>
</section>
