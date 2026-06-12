@props(['history' => []])

<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <p class="text-sm font-extrabold text-stone-950">Recent connection tests</p>
    <div class="mt-3 space-y-2">
        @forelse ($history as $item)
            <div class="flex flex-col gap-1 rounded-md bg-white p-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                <span class="font-bold text-stone-700">{{ $item['message'] }}</span>
                <span class="text-xs font-bold text-stone-500">{{ isset($item['tested_at']) ? \Illuminate\Support\Carbon::parse($item['tested_at'])->diffForHumans() : 'Recently' }}</span>
            </div>
        @empty
            <p class="text-sm text-stone-600">No provider tests recorded yet.</p>
        @endforelse
    </div>
</div>
