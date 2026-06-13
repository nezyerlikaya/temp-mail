@props(['event'])

<div class="p-3">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <x-integrations.connection-badge :status="$event['status'] ?? 'not_tested'" />
        <span class="text-xs font-bold text-stone-500">{{ $event['tested_at'] ?? 'Pending' }}</span>
    </div>
    <p class="mt-2 text-sm font-semibold text-stone-700">{{ $event['message'] ?? 'Connection test completed.' }}</p>
    <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-stone-500">
        <span>{{ $event['environment'] ?? 'sandbox' }}</span>
        <span>{{ $event['duration_ms'] ?? 0 }} ms</span>
        @if (! empty($event['error_code']))
            <span>{{ $event['error_code'] }}</span>
        @endif
    </div>
</div>
