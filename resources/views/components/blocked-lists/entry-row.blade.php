@props(['entry', 'types', 'canUpdate', 'canToggle', 'canViewSensitiveIp', 'canViewRelatedAbuseCase'])
<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-blocked-lists.type-badge :type="$entry->entry_type" />
                <x-blocked-lists.status-badge :status="$entry->status" />
                <x-blocked-lists.source-badge :source="$entry->source" />
                <x-blocked-lists.expiration-badge :entry="$entry" />
                <x-blocked-lists.expiry-warning :entry="$entry" />
            </div>
            <h3 class="mt-3 break-words text-base font-extrabold text-stone-950">
                @if ($entry->entry_type === 'ip_address' && $canViewSensitiveIp)
                    {{ $entry->encrypted_normalized_value }}
                @else
                    {{ $entry->display_value }}
                @endif
            </h3>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600">{{ $entry->reason }}</p>
            <div class="mt-3 flex flex-wrap gap-3 text-xs font-semibold text-stone-500">
                <span>Created by {{ $entry->creator?->name ?? 'System' }}</span>
                <span>Starts {{ $entry->starts_at?->format('M j, Y') ?? 'immediately' }}</span>
                @if ($entry->abuseReport)
                    @if ($canViewRelatedAbuseCase)
                        <a href="{{ route('admin.abuse-reports.show', $entry->abuseReport) }}" class="text-teal-800 underline decoration-teal-300 underline-offset-4">Case {{ $entry->abuseReport->case_reference }}</a>
                    @else
                        <span>Related abuse case protected</span>
                    @endif
                @endif
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            @if ($canUpdate)
                <a href="{{ route('admin.blocked-lists.index', ['group' => request('group', 'senders'), 'edit' => $entry->id]) }}" class="inline-flex min-h-9 items-center rounded-lg border border-stone-300 px-3 text-xs font-extrabold text-stone-700">Edit</a>
            @endif
            @if ($canToggle && $entry->status !== 'active')
                <form method="POST" action="{{ route('admin.blocked-lists.activate', $entry) }}">@csrf<button class="inline-flex min-h-9 items-center rounded-lg bg-emerald-700 px-3 text-xs font-extrabold text-white">Activate</button></form>
            @endif
            @if ($canToggle && $entry->status === 'active')
                <form method="POST" action="{{ route('admin.blocked-lists.deactivate', $entry) }}">@csrf<button class="inline-flex min-h-9 items-center rounded-lg border border-amber-300 px-3 text-xs font-extrabold text-amber-800">Deactivate</button></form>
            @endif
        </div>
    </div>
</article>
