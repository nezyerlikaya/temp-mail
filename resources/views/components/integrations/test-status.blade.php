@props(['status', 'lastTestedAt' => null])

<div class="flex flex-wrap items-center justify-between gap-2 rounded-md border border-white bg-white p-3 ring-1 ring-stone-200">
    <div>
        <p class="text-xs font-extrabold uppercase text-stone-500">Current status</p>
        <div class="mt-1">
            <x-integrations.connection-badge :status="$status" />
        </div>
    </div>
    <p class="text-xs font-bold text-stone-500">
        {{ $lastTestedAt ? 'Last tested '.$lastTestedAt->diffForHumans() : 'Not tested yet' }}
    </p>
</div>
