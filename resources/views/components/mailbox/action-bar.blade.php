@props(['mailbox', 'message', 'canManage' => false])
<x-admin.card title="Message actions" description="Update the administrative read state without changing message content.">
    <form method="POST" action="{{ route('admin.mailbox-operations.messages.action', [$mailbox, $message]) }}" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" class="space-y-3">
        @csrf
        <input type="hidden" name="action" value="{{ $message->isUnread() ? 'read' : 'unread' }}">
        <button type="submit" @disabled(! $canManage) class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-stone-900 px-4 text-sm font-extrabold text-white hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-600/30 disabled:cursor-not-allowed disabled:bg-stone-400">Mark {{ $message->isUnread() ? 'read' : 'unread' }}</button>
    </form>
</x-admin.card>
