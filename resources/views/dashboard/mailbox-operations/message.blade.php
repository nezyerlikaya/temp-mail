<x-admin.layout title="Message Details" :user="$adminUser">
    <x-admin.page-header eyebrow="Mailbox Operations" title="{{ $message->subject ?: 'No subject' }}" description="Private message content for {{ $mailbox->address }}." />

    @if(session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-mailbox.privacy-warning />

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="min-w-0 space-y-6">
            <x-mailbox.message-preview :message="$message" />
            <x-mailbox.message-headers :headers="$message->raw_headers ?? []" />
        </div>
        <aside class="space-y-6">
            <x-mailbox.action-bar :mailbox="$mailbox" :message="$message" :can-manage="$canManageMessage" />
            <x-mailbox.delete-warning :mailbox="$mailbox" :message="$message" :can-delete="$canManageMessage" />
        </aside>
    </div>
</x-admin.layout>
