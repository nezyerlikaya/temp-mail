@props(['entry'])
@if ($entry->expires_at)
    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold {{ $entry->expires_at->isPast() ? 'bg-amber-50 text-amber-900 ring-1 ring-amber-200' : 'bg-sky-50 text-sky-800 ring-1 ring-sky-100' }}">{{ $entry->expires_at->isPast() ? 'Expired' : 'Expires' }} {{ $entry->expires_at->format('M j, Y') }}</span>
@else
    <span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-600">No expiry</span>
@endif
