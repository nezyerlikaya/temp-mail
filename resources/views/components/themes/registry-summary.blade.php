@props(['count'])

<section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-extrabold uppercase text-teal-700">Fixed Theme Registry</p>
            <h2 class="mt-1 text-xl font-extrabold text-stone-950">Public website themes</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-stone-600">Theme Launch Center controls the public renderer only. The admin panel keeps its stable operations layout regardless of the active public theme.</p>
        </div>
        <div class="rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-extrabold text-stone-950">
            {{ $count }} registered
        </div>
    </div>
</section>
