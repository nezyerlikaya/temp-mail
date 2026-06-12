<x-admin.layout title="Edit SMTP Connection" :user="$adminUser">
    <x-admin.page-header eyebrow="Mail Infrastructure" title="{{ $connection->name }}" description="Manage transactional SMTP delivery readiness for {{ $connection->from_email }}." />

    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    @if (session('error'))<x-admin.alert variant="danger" class="mb-6">{{ session('error') }}</x-admin.alert>@endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">
        <x-mail.smtp-connection-editor :connection="$connection" :domains="$domains" :encryption-options="$encryptionOptions" />
        <aside class="space-y-6">
            <x-mail.smtp-test-panel :connection="$connection" :can-test="$canTest" :can-send-test="$canSendTest" />
            <x-mail.health-history :history="$connection->health_history ?? []" />
        </aside>
    </div>
</x-admin.layout>
