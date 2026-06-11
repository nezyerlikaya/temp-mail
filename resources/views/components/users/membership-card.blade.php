@props(['membership'])

<x-admin.card title="Membership Summary" description="Read-only readiness for the future Plans & Memberships module.">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $membership['plan'] }}</p>
            <p class="mt-1 text-xs text-stone-500">Plans never grant admin roles or permissions.</p>
        </div>
        <x-users.membership-status-badge :status="$membership['status']" />
    </div>
    <dl class="mt-5 grid gap-4 border-t border-stone-200 pt-4 text-sm sm:grid-cols-3">
        <div>
            <dt class="text-stone-500">Premium starts</dt>
            <dd class="mt-1 font-bold text-stone-900">{{ $membership['starts_at'] ?: 'Not scheduled' }}</dd>
        </div>
        <div>
            <dt class="text-stone-500">Premium ends</dt>
            <dd class="mt-1 font-bold text-stone-900">{{ $membership['ends_at'] ?: 'No end date' }}</dd>
        </div>
        <div>
            <dt class="text-stone-500">Granted by</dt>
            <dd class="mt-1 font-bold text-stone-900">{{ $membership['granted_by'] ?: 'Not granted' }}</dd>
        </div>
    </dl>
</x-admin.card>
