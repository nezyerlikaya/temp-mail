<x-admin.layout title="Edit Inbound Connection" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Mail Infrastructure"
        title="{{ $connection->name }}"
        description="Manage secure inbound access for {{ $connection->domain->domain_name }}."
    />

    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    @if (session('error'))<x-admin.alert variant="danger" class="mb-6">{{ session('error') }}</x-admin.alert>@endif

    <div class="space-y-6">
        <x-mail.extension-warning :extension="$extension" />
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">
            <x-mail.inbound-connection-editor :connection="$connection" :domains="$domains" :encryption-options="$encryptionOptions" />
            <aside class="space-y-6">
                <x-mail.connection-test-panel :connection="$connection" :can-test="$canTest" :extension="$extension" />
                <x-mail.health-history :history="$connection->health_history ?? []" />
            </aside>
        </div>
    </div>
</x-admin.layout>
