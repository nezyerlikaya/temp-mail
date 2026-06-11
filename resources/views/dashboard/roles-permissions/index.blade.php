<x-admin.layout title="Roles & Permissions" :user="$adminUser">
    <x-admin.page-header
        eyebrow="People"
        title="Roles & Permissions"
        description="Control admin panel access independently from product plans and premium membership."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <x-users.protection-warning class="mb-6" title="Critical access is protected">
        Owner accounts cannot be deleted. The last owner or administrator cannot lose access, and you cannot downgrade your own protected role.
    </x-users.protection-warning>

    <section aria-labelledby="role-overview-title">
        <div class="mb-4">
            <h2 id="role-overview-title" class="text-lg font-extrabold text-stone-950">Role overview</h2>
            <p class="mt-1 text-sm text-stone-600">Six fixed roles keep permission decisions predictable and auditable.</p>
        </div>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roleSummaries as $summary)
                <x-users.role-card :summary="$summary" />
            @endforeach
        </div>
    </section>

    <x-admin.card class="mt-6" title="Permission matrix" description="Allowed modules are visible in the sidebar and Ctrl+K command palette.">
        <x-users.permission-matrix :matrix="$permissionMatrix" :roles="$roles" />
    </x-admin.card>

    <x-admin.card class="mt-6" title="Role assignments" description="Changing a role updates admin access immediately and records an audit event.">
        <div class="divide-y divide-stone-200">
            @foreach ($users as $profileUser)
                <div class="py-4 first:pt-0 last:pb-0">
                    <x-users.role-selector :profile-user="$profileUser" :role-options="$roleOptions" :actor-role="$adminUser->role" />
                </div>
            @endforeach
        </div>

        @if ($users->hasPages())
            <div class="mt-5 border-t border-stone-200 pt-5">{{ $users->links() }}</div>
        @endif
    </x-admin.card>
</x-admin.layout>
