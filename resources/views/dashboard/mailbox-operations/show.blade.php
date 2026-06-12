<x-admin.layout title="Mailbox Details" :user="$adminUser">
    <x-admin.page-header eyebrow="Mailbox Operations" title="{{ $mailbox->address }}" description="Lifecycle and ownership metadata foundation. Message list and content arrive in a later step." />
    @if(session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]"><x-mailbox.detail-summary :mailbox="$mailbox" /><aside><x-mailbox.timeline :timeline="$timeline" /></aside></div>
</x-admin.layout>
