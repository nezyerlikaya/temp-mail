@props(['entry'])
@if ($entry->expires_at && $entry->status === 'active' && $entry->expires_at->isFuture() && $entry->expires_at->diffInDays(now()) <= 7)
    <span class="rounded-md bg-amber-50 px-2 py-1 text-xs font-extrabold text-amber-800">Expiring soon</span>
@endif
