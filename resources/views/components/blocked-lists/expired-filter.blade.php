@props(['filters'])
<a href="{{ route('admin.blocked-lists.index', ['group' => $filters['group'], 'expiry' => 'expiring_soon']) }}" class="inline-flex min-h-9 items-center rounded-lg border border-amber-300 px-3 text-xs font-extrabold text-amber-800">Expiring soon</a>
