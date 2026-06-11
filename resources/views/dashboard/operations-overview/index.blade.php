<x-admin.layout title="Operations Overview" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Workspace"
        title="Operations Overview"
        description="Monitor the foundation of your Temp Mail SaaS workspace and prepare for operational modules."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Active" />
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert variant="success" title="Admin shell is ready" class="mb-6">
        Authentication, admin authorization, responsive navigation, and the shared operations layout are active.
    </x-admin.alert>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]">
        <x-admin.card title="Operational workspace" description="Live operational modules will appear here as they are enabled.">
            <x-admin.empty-state
                title="No operational signals yet"
                description="Mailbox activity, infrastructure health, and product analytics will populate this workspace in later module steps."
            />
        </x-admin.card>

        <x-admin.card title="Foundation status" description="Core controls available in this release.">
            <dl class="divide-y divide-stone-200">
                <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                    <dt class="text-sm font-semibold text-stone-600">Admin access</dt>
                    <dd><x-admin.status-badge status="Active" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-sm font-semibold text-stone-600">Installer</dt>
                    <dd><x-admin.status-badge status="Locked" /></dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                    <dt class="text-sm font-semibold text-stone-600">Operations data</dt>
                    <dd><x-admin.status-badge status="Draft" /></dd>
                </div>
            </dl>
        </x-admin.card>
    </div>
</x-admin.layout>
