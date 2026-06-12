@props(['notification', 'actionLink'])

<x-admin.card title="Notification detail" description="Readiness view for the selected event.">
    @if ($notification)
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <x-notifications.severity-badge :severity="$notification->severity" />
                <x-notifications.unread-badge :unread="$notification->isUnread()" />
                <x-notifications.deduplication-badge :notification="$notification" />
                @if ($notification->isArchived())
                    <span class="rounded-full bg-stone-900 px-2 py-1 text-xs font-extrabold text-white">Archived</span>
                @endif
            </div>

            <div>
                <h2 class="text-lg font-extrabold text-stone-950">{{ $notification->title }}</h2>
                <p class="mt-2 text-sm leading-6 text-stone-600">{{ $notification->message }}</p>
            </div>

            <dl class="grid gap-3 text-sm">
                <div class="rounded-lg bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">Event key</dt>
                    <dd class="mt-1 font-extrabold text-stone-900">{{ $notification->event_key }}</dd>
                </div>
                <div class="rounded-lg bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">Target readiness</dt>
                    <dd class="mt-1 font-extrabold text-stone-900">
                        @if ($notification->target_type && $notification->target_id)
                            {{ class_basename($notification->target_type) }} #{{ $notification->target_id }}
                        @else
                            No target attached
                        @endif
                    </dd>
                </div>
                <div class="rounded-lg bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">Email delivery</dt>
                    <dd class="mt-1 font-extrabold text-stone-900">{{ str($notification->email_status ?? 'not attempted')->headline() }}</dd>
                </div>
                <div class="rounded-lg bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">Occurrence window</dt>
                    <dd class="mt-1 font-extrabold text-stone-900">
                        {{ $notification->first_occurred_at?->format('M j, Y H:i') ?? $notification->created_at->format('M j, Y H:i') }}
                        -
                        {{ $notification->last_occurred_at?->format('M j, Y H:i') ?? $notification->updated_at->format('M j, Y H:i') }}
                    </dd>
                </div>
                <div class="rounded-lg bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">Snooze</dt>
                    <dd class="mt-1 font-extrabold text-stone-900">{{ $notification->snoozed_until?->format('M j, Y H:i') ?? 'Not snoozed' }}</dd>
                </div>
            </dl>

            <div class="flex flex-wrap gap-2">
                <x-notifications.action-link :link="$actionLink" />
                @if ($notification->isUnread())
                    <form method="POST" action="{{ route('admin.notifications.mark-read', $notification) }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Mark read</button>
                    </form>
                @endif
                @unless ($notification->isArchived())
                    <form method="POST" action="{{ route('admin.notifications.archive', $notification) }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Archive</button>
                    </form>
                @endunless
            </div>

            <x-notifications.snooze-menu :notification="$notification" />
        </div>
    @else
        <x-admin.empty-state title="Select a notification" description="Open a feed item to review target readiness, delivery status, and the permission-aware action link." />
    @endif
</x-admin.card>
