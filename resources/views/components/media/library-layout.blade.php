@props(['summary' => []])

<section {{ $attributes->merge(['class' => 'space-y-6']) }}>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-stone-500">Assets</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $summary['total'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-stone-500">Images</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $summary['images'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-stone-500">Documents</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $summary['documents'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-stone-500">Hidden</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $summary['hidden'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-5 shadow-sm">
            <p class="text-sm font-bold text-red-700">Trashed</p>
            <p class="mt-2 text-2xl font-extrabold text-red-950">{{ $summary['trashed'] ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-stone-500">Orphaned</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $summary['orphaned'] ?? 0 }}</p>
        </div>
    </div>

    {{ $slot }}
</section>
