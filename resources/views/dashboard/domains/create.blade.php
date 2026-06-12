<x-admin.layout title="Create Domain" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Mail Infrastructure"
        title="Add receiving domain"
        description="Create the domain foundation before running public DNS readiness checks."
    />

    @if ($errors->any())
        <x-admin.alert variant="danger" class="mb-6" title="Domain settings need attention">
            Review the highlighted fields and try again.
        </x-admin.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <x-domains.domain-editor :domain="$domain" :statuses="$statuses" :can-update="$canUpdateDomain" />

        <aside class="space-y-6">
            <x-admin.card title="Readiness boundaries" description="This step prepares domain operations only.">
                <div class="space-y-3 text-sm leading-6 text-stone-600">
                    <p>DNS readiness does not configure IMAP, SMTP, or inbound message processing.</p>
                    <p>Catch-all readiness can be marked only after routing is confirmed with the mail provider.</p>
                    <p>No DNS provider credentials or infrastructure secrets are stored.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
