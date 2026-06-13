@props(['title', 'description'])

<div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 p-8 text-center">
    <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-white text-stone-600 ring-1 ring-stone-200">
        <i data-lucide="plug-zap" class="size-5" aria-hidden="true"></i>
    </div>
    <h3 class="mt-3 text-base font-extrabold text-stone-950">{{ $title }}</h3>
    <p class="mx-auto mt-1 max-w-md text-sm font-semibold text-stone-600">{{ $description }}</p>
</div>
