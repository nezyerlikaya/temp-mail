@props(['title', 'description'])

<div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 p-5 text-center">
    <div class="mx-auto grid size-10 place-items-center rounded-md bg-white text-stone-600 ring-1 ring-stone-200">
        <i data-lucide="circle-dashed" class="size-5" aria-hidden="true"></i>
    </div>
    <p class="mt-3 text-sm font-extrabold text-stone-950">{{ $title }}</p>
    <p class="mt-1 text-sm font-semibold text-stone-600">{{ $description }}</p>
</div>
