@props(['title', 'description'])

<div {{ $attributes->merge(['class' => 'flex min-h-56 flex-col items-center justify-center px-5 py-10 text-center']) }}>
    <span class="grid size-12 place-items-center rounded-full bg-stone-100 text-stone-600" aria-hidden="true">
        <i data-lucide="inbox" class="size-6"></i>
    </span>
    <h3 class="mt-4 text-base font-extrabold text-stone-950">{{ $title }}</h3>
    <p class="mt-2 max-w-md text-sm leading-6 text-stone-600">{{ $description }}</p>
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
