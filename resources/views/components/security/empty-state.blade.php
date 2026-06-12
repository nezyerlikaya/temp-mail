@props(['title' => 'No security signals', 'message' => 'No signals match the current filters.'])

<div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 px-6 py-10 text-center">
    <p class="text-base font-extrabold text-stone-950">{{ $title }}</p>
    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-stone-600">{{ $message }}</p>
</div>
