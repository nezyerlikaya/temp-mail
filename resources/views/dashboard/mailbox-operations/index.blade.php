<x-admin.layout title="Mailbox Operations" :user="$adminUser">
    <x-admin.page-header eyebrow="Workspace" title="Mailbox Operations" description="Monitor temporary inbox lifecycle, ownership readiness, expiration state, and delivery metadata without exposing private messages.">
        <x-slot:actions>@if($canCreateMailbox)<a href="{{ route('admin.mailbox-operations.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20"><i data-lucide="plus" class="size-4" aria-hidden="true"></i>Create mailbox</a>@endif</x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <x-mailbox.metric-card label="Active inboxes" :value="$metrics['active']" detail="Currently available mailbox records." icon="inbox" />
        <x-mailbox.metric-card label="Created today" :value="$metrics['created_today']" detail="Mailbox creation volume today." icon="calendar-plus" />
        <x-mailbox.metric-card label="Expired inboxes" :value="$metrics['expired']" detail="Lifecycle expiry queue." icon="clock-alert" />
        <x-mailbox.metric-card label="Locked inboxes" :value="$metrics['locked']" detail="Administrative review readiness." icon="lock-keyhole" />
        <x-mailbox.metric-card label="Emails today" :value="$metrics['emails_today']" detail="Message ingestion metric hook." icon="mail-check" />
        <x-mailbox.metric-card label="Delivery health" :value="$metrics['delivery_health']" detail="Mail infrastructure integration hook." icon="activity" />
    </div>

    <div class="mt-6"><x-mailbox.filter-bar :filters="$filters" :statuses="$statuses" :types="$types" :domains="$domains" /></div>

    <x-admin.card class="mt-6" title="Inbox lifecycle queue" description="Paginated mailbox metadata for operational review.">
        @if($mailboxes->count())
            <div class="-mx-5 -my-4 hidden sm:block sm:-mx-6">@foreach($mailboxes as $mailbox)<x-mailbox.inbox-row :mailbox="$mailbox" />@endforeach</div>
            <div class="grid gap-3 sm:hidden">@foreach($mailboxes as $mailbox)<x-mailbox.inbox-card :mailbox="$mailbox" />@endforeach</div>
            <x-admin.pagination :paginator="$mailboxes" class="mt-5" />
        @else
            <x-mailbox.empty-state :can-create="$canCreateMailbox" />
        @endif
    </x-admin.card>
</x-admin.layout>
