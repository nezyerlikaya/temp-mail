<x-admin.layout :user="auth()->user()" title="Blocked Lists">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="Blocked Lists" description="Reviewed enforcement entries for senders, recipients, IP rules, comments, and phrase readiness." />
    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-error-summary />
    <x-blocked-lists.layout :summary="$summary" :notification-readiness="$notificationReadiness">
        <x-blocked-lists.type-tabs :groups="$groups" :active="$filters['group']" />
        <x-blocked-lists.filter-bar :filters="$filters" :types="$types" :statuses="$statuses" :sources="$sources" :administrators="$administrators" />
        <div class="flex flex-wrap gap-2">
            <x-blocked-lists.expired-filter :filters="$filters" />
        </div>
        <x-blocked-lists.bulk-actions :entries="$entries" :can-bulk-modify="$canBulkModify" />
        <section class="grid gap-5 xl:grid-cols-[1fr_420px]">
            <div class="space-y-3">
                @forelse ($entries as $entry)
                    <x-blocked-lists.entry-row :entry="$entry" :types="$types" :can-update="$canUpdate" :can-toggle="$canToggle" :can-view-sensitive-ip="$canViewSensitiveIp" :can-view-related-abuse-case="$canViewRelatedAbuseCase" />
                @empty
                    <x-blocked-lists.empty-state />
                @endforelse
                {{ $entries->links() }}
            </div>
            <div class="space-y-5">
                <x-blocked-lists.entry-editor :entry="$editEntry" :types="$types" :statuses="$statuses" :sources="$sources" :can-create="$canCreate" :can-update="$canUpdate" />
                <x-blocked-lists.enforcement-test :types="$types" :can-run="$canRunEnforcementTest" :result="$matchResult" />
                <x-blocked-lists.import-panel :can-import="$canImport" :preview="$importPreview" />
                <x-blocked-lists.export-panel :can-export="$canExport" :filters="$filters" :can-view-sensitive-ip="$canViewSensitiveIp" />
            </div>
        </section>
    </x-blocked-lists.layout>
</x-admin.layout>
