<x-admin.layout title="Create SMTP Connection" :user="$adminUser">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="Add SMTP Connection" description="Configure transactional outbound delivery for system emails." />

    <x-mail.smtp-connection-editor :connection="$connection" :domains="$domains" :encryption-options="$encryptionOptions" />
</x-admin.layout>
