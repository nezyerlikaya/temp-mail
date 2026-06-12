@props(['membership'])
<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-extrabold text-stone-950">{{ $membership->user->email }}</p>
        <x-billing.membership-status-badge :status="$membership->status" />
    </div>
    <p class="mt-1 text-sm text-stone-600">{{ $membership->plan->name }} · Ends {{ $membership->ends_at?->diffForHumans() ?? 'without expiration' }}</p>
</div>
