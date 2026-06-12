<x-admin.layout title="Mailbox Details" :user="$adminUser">
    <x-admin.page-header eyebrow="Mailbox Operations" title="{{ $mailbox->address }}" description="Review delivery activity, message metadata, and mailbox lifecycle controls." />

    @if(session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    @if($errors->any())
        <x-admin.alert variant="danger" class="mb-6" role="alert">
            <p class="font-extrabold">The action could not be completed.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-admin.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="min-w-0 space-y-6">
            <x-mailbox.detail-summary :mailbox="$mailbox" />
            <x-mailbox.message-list :mailbox="$mailbox" :messages="$messages" :can-view-content="$canViewMessageContent" />
        </div>
        <aside class="space-y-6">
            <x-mailbox.lifecycle-actions :mailbox="$mailbox" :can-expire="$canExpireMailbox" :can-lock="$canLockMailbox" :can-empty="$canEmptyMailbox" />
            <x-mailbox.timeline :timeline="$timeline" />
        </aside>
    </div>
</x-admin.layout>
