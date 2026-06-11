@props(['title', 'description' => null, 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-3xl">
        @if ($eyebrow)
            <p class="text-xs font-bold uppercase text-teal-700">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-1 text-2xl font-extrabold text-stone-950 sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 text-sm leading-6 text-stone-600 sm:text-base">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-3">{{ $actions }}</div>
    @endisset
</div>
