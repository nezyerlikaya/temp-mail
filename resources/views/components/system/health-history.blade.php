@props(['history'])

<x-admin.card title="Health History" description="The latest 10 health check runs. Stored history is pruned to avoid unbounded growth.">
    @if ($history->count() > 0)
        <div class="space-y-3">
            @foreach ($history as $record)
                <div class="flex items-start justify-between gap-4 rounded-md border border-stone-200 p-3">
                    <div>
                        <p class="text-sm font-extrabold text-stone-950">{{ $record->checked_at?->format('M j, Y H:i') }}</p>
                        <p class="mt-1 text-xs text-stone-500">By {{ $record->checker?->name ?? 'System' }}</p>
                    </div>
                    <div class="text-right">
                        <x-system.health-status-badge :status="$record->overall_status" />
                        <p class="mt-2 text-xs text-stone-500">{{ $record->summary['critical'] ?? 0 }} critical</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-system.empty-state title="No health history yet" description="Run the first health check to store a snapshot. The page still shows a safe live preview." class="min-h-48" />
    @endif
</x-admin.card>
