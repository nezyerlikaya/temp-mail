<x-admin.layout :user="auth()->user()" title="Blocked Lists">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="Blocked Lists" description="Reviewed enforcement entries for senders, recipients, IP rules, comments, and phrase readiness." />
    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-error-summary />
    <x-blocked-lists.layout :summary="$summary" :notification-readiness="$notificationReadiness">
        <x-blocked-lists.type-tabs :groups="$groups" :active="$filters['group']" />
        <x-blocked-lists.filter-bar :filters="$filters" :types="$types" :statuses="$statuses" :sources="$sources" :administrators="$administrators" />
        <section class="grid gap-5 xl:grid-cols-[1fr_420px]">
            <div class="space-y-3">
                @forelse ($entries as $entry)
                    <x-blocked-lists.entry-row :entry="$entry" :types="$types" :can-update="$canUpdate" :can-toggle="$canToggle" :can-view-sensitive-ip="$canViewSensitiveIp" :can-view-related-abuse-case="$canViewRelatedAbuseCase" />
                @empty
                    <x-blocked-lists.empty-state />
                @endforelse
                {{ $entries->links() }}
            </div>
            <x-blocked-lists.entry-editor :entry="$editEntry" :types="$types" :statuses="$statuses" :sources="$sources" :can-create="$canCreate" :can-update="$canUpdate" />
        </section>
    </x-blocked-lists.layout>
</x-admin.layout>
