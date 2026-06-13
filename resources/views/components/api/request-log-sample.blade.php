@props(['logs'])

<x-admin.card title="Safe request log" description="Last 10 requests without bodies, secrets, message content, or sensitive headers.">
    <div class="space-y-3">
        @forelse($logs as $log)
            <div class="rounded-lg border border-stone-200 p-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-extrabold text-stone-500">{{ $log->method }} · {{ $log->response_status }}</p>
                    <p class="text-xs font-bold text-stone-500">{{ $log->duration_ms }} ms</p>
                </div>
                <p class="mt-1 break-all text-sm font-extrabold text-stone-950">{{ $log->endpoint }}</p>
                <p class="mt-1 text-xs font-bold text-stone-500">{{ $log->key_prefix ?? 'No key' }} · {{ $log->requested_at?->diffForHumans() }}</p>
            </div>
        @empty
            <x-api.empty-state title="No API requests yet" description="Authenticated requests will appear here after API keys are used." class="min-h-32 py-6" />
        @endforelse
    </div>
</x-admin.card>
