<x-admin.layout title="Integrations Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Growth"
        title="Integrations Center"
        description="Secure configuration readiness for external services. Production clients, sync jobs, and checkout flows are intentionally separate."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="mb-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-integrations.category-tabs :categories="$categories" :active-category="$activeCategory" :environment="$activeEnvironment" />
        <x-integrations.environment-selector :environments="$environments" :active-environment="$activeEnvironment" :active-category="$activeCategory" :selected="$selected['key']" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_430px]">
        <div class="space-y-6">
            @if ($integrations->isEmpty())
                <x-integrations.empty-state title="No integrations in this category" description="Choose another category to review supported providers." />
            @else
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($integrations as $integration)
                        <x-integrations.integration-card :integration="$integration" :environment="$activeEnvironment" :active="$selected['key'] === $integration['key']" />
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <x-admin.card :title="$selected['name']" :description="$selected['description']">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <x-integrations.status-badge :active="$selected['is_active']" />
                    <x-integrations.connection-badge :status="$selected['connection_status']" />
                    <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-stone-200">{{ $environments[$selected['environment']] }}</span>
                </div>

                <x-integrations.provider-readiness :summary="$healthSummary" :environment="$environments[$activeEnvironment]" />

                <x-integrations.dependency-warning :warnings="$dependencyWarnings" />

                <x-integrations.required-fields-checklist :items="$selected['checklist']" />

                @if ($canViewHealth)
                    <x-integrations.test-panel
                        :integration="$selected"
                        :environment="$activeEnvironment"
                        :history="$healthHistory"
                        :can-test="$canTest"
                    />
                @endif

                <x-integrations.integration-settings-form
                    :integration="$selected"
                    :environment="$activeEnvironment"
                    :can-configure="$canConfigure"
                    :can-toggle="$canToggle"
                    :can-reveal="$canReveal"
                />
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
