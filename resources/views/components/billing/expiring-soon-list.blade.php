@props(['memberships'])
<x-admin.card title="Expiring soon" description="Memberships ending within seven days. Notifications are readiness-backed.">
    <div class="space-y-3">
        @forelse($memberships as $membership)
            <x-billing.membership-card :membership="$membership" />
        @empty
            <p class="text-sm text-stone-600">No memberships are expiring soon.</p>
        @endforelse
    </div>
</x-admin.card>
