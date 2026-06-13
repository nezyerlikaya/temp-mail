@props(['history'])

<div class="mt-4">
    <div class="mb-2 flex items-center justify-between gap-3">
        <p class="text-xs font-extrabold uppercase text-stone-500">Last five results</p>
        <span class="text-xs font-bold text-stone-500">{{ $history->count() }}/5</span>
    </div>

    @if ($history->isEmpty())
        <x-integrations.empty-state title="No test history yet" description="Run a manual test to record the first safe provider readiness event." />
    @else
        <div class="divide-y divide-stone-200 overflow-hidden rounded-md border border-stone-200 bg-white">
            @foreach ($history as $event)
                <x-integrations.health-event-row :event="$event" />
            @endforeach
        </div>
    @endif
</div>
