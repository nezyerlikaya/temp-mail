<x-admin.layout title="Create Inbound Connection" :user="$adminUser">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="Add Inbound Connection" description="Attach a receiving domain to an IMAP-compatible provider without enabling message ingestion." />

    <div class="space-y-6">
        <x-mail.extension-warning :extension="$extension" />
        <x-mail.inbound-connection-editor :connection="$connection" :domains="$domains" :encryption-options="$encryptionOptions" />
    </div>
</x-admin.layout>
