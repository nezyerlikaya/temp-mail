@props(['summary', 'canView' => false])

<x-admin.card title="Failed login history" description="Readiness view from audit logs; session identifiers are never displayed.">
    <div class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 bg-stone-50 p-4">
        <div>
            <p class="text-2xl font-extrabold text-stone-950">{{ $canView ? $summary['count_24h'] : '—' }}</p>
            <p class="mt-1 text-sm font-semibold text-stone-600">{{ $canView ? $summary['message'] : 'Owner or admin access is required.' }}</p>
        </div>
        <x-security.status-badge :status="$canView ? $summary['status'] : 'passive'" />
    </div>

    @if ($canView)
        <div class="mt-4 space-y-3">
            @forelse ($summary['recent'] as $event)
                <div class="rounded-lg border border-stone-200 bg-white p-3">
                    <p class="text-sm font-bold text-stone-900">{{ $event['email'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-stone-500">{{ str($event['reason'])->headline() }} · {{ $event['created_at']->diffForHumans() }}</p>
                </div>
            @empty
                <p class="rounded-lg border border-stone-200 bg-white p-4 text-sm font-semibold text-stone-600">No failed login records yet.</p>
            @endforelse
        </div>
    @endif
</x-admin.card>
