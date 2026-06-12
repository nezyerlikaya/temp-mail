@props(['notification', 'selected' => false])

<article class="rounded-lg border bg-white p-4 shadow-sm transition {{ $selected ? 'border-teal-500 ring-4 ring-teal-600/10' : 'border-stone-200 hover:border-stone-300' }}">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-notifications.severity-badge :severity="$notification->severity" />
                <x-notifications.unread-badge :unread="$notification->isUnread()" />
                @if ($notification->related_module)
                    <span class="rounded-full bg-stone-100 px-2 py-1 text-xs font-bold text-stone-600">{{ str($notification->related_module)->replace('-', ' ')->headline() }}</span>
                @endif
            </div>
            <h3 class="mt-3 text-base font-extrabold text-stone-950">
                <a href="{{ route('admin.notifications.show', $notification) }}" class="rounded-sm focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    {{ $notification->title }}
                </a>
            </h3>
            <p class="mt-1 line-clamp-2 text-sm leading-6 text-stone-600">{{ $notification->message }}</p>
            <p class="mt-3 text-xs font-bold text-stone-500">{{ $notification->created_at->diffForHumans() }}</p>
        </div>

        <div class="flex shrink-0 gap-2">
            @if ($notification->isUnread())
                <form method="POST" action="{{ route('admin.notifications.mark-read', $notification) }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-9 items-center rounded-md border border-stone-300 px-3 text-xs font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                        Mark read
                    </button>
                </form>
            @endif
            @unless ($notification->isArchived())
                <form method="POST" action="{{ route('admin.notifications.archive', $notification) }}">
                    @csrf
                    <button type="submit" class="inline-flex min-h-9 items-center rounded-md border border-stone-300 px-3 text-xs font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                        Archive
                    </button>
                </form>
            @endunless
        </div>
    </div>
</article>
