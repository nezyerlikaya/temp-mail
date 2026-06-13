<x-admin.layout title="API Access" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Growth"
        title="API Access"
        description="Manage scoped Temp Mail API keys without mixing product access with admin roles."
    />

    @if(session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    @if(session('api_secret_once'))
        <x-api.secret-reveal-panel :secret="session('api_secret_once')" class="mb-6" />
    @endif
    @if($errors->any())
        <x-admin.alert variant="danger" class="mb-6" role="alert">
            <p class="font-extrabold">Review the API access form.</p>
            <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-admin.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <x-api.usage-cards :summary="$usageSummary" />
            <x-api.create-key-panel
                :users="$users"
                :scopes="$scopes"
                :can-create="$canCreateOwnKey || $canManageGlobally"
                :can-manage-globally="$canManageGlobally"
            />

            <x-admin.card title="API keys" description="Secrets are stored as secure hashes. Full key values are never shown after creation or regeneration.">
                @forelse($keys as $key)
                    <x-api.key-row :api-key="$key" :can-manage="$canManageGlobally || $adminUser->is($key->user)" />
                @empty
                    <x-api.empty-state />
                @endforelse
                @if($keys->hasPages())<div class="mt-5">{{ $keys->links() }}</div>@endif
            </x-admin.card>

            <x-api.documentation-panel :documentation="$documentation" />
        </div>

        <aside class="space-y-6">
            <x-api.status-card :settings="$settings" :can-manage="$canManageGlobally" />
            <x-api.request-log-sample :logs="$requestLogs" />
            <x-admin.card title="Security boundary" description="API scopes control programmatic Temp Mail access only. They never grant owner, admin, editor, moderator, or author permissions." />
            <x-admin.card title="Deferred from this MVP" description="OAuth apps, SDK generation, API webhooks, marketplace workflows, and a full developer portal remain intentionally out of scope." />
        </aside>
    </div>
</x-admin.layout>
