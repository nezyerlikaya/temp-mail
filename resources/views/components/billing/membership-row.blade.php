@props(['membership', 'canExtend' => false, 'canCancel' => false])
<article class="space-y-4 border-b border-stone-200 py-5 last:border-b-0">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <p class="font-extrabold text-stone-950">{{ $membership->user->name }}</p>
                <x-billing.membership-status-badge :status="$membership->status" />
            </div>
            <p class="mt-1 text-sm font-bold text-stone-700">{{ $membership->user->email }} · {{ $membership->plan->name }}</p>
            <p class="mt-1 text-sm text-stone-600">{{ $membership->starts_at->toDayDateTimeString() }} → {{ $membership->ends_at?->toDayDateTimeString() ?? 'No end date' }}</p>
        </div>
        @if($membership->status === 'expired')
            <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm font-bold text-red-800">Expired but not yet processed warning</div>
        @endif
    </div>
    <x-billing.extend-membership-panel :membership="$membership" :can-extend="$canExtend" />
    <x-billing.cancel-warning :membership="$membership" :can-cancel="$canCancel" />
</article>
