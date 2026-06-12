@props(['notifications', 'selectedNotification' => null])

<div class="space-y-3">
    @forelse ($notifications as $notification)
        <x-notifications.feed-item :notification="$notification" :selected="$selectedNotification?->is($notification)" />
    @empty
        <x-notifications.empty-state />
    @endforelse
</div>
