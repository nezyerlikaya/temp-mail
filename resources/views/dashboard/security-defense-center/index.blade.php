<x-admin.layout title="Security Defense Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Trust"
        title="Security Defense Center"
        description="Configure bot providers, rate limits, and administrator access safeguards without risking admin lockout."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    @if (session('test_status'))
        <x-admin.alert :variant="session('test_status.status') === 'failed' ? 'warning' : 'success'" class="mb-6" title="Connection test">
            {{ session('test_status.message') }}
        </x-admin.alert>
    @endif

    @if ($errors->any())
        <x-admin.alert variant="danger" class="mb-6" title="Security settings need attention">
            Review the highlighted provider fields and try again.
        </x-admin.alert>
    @endif

    <div class="mb-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([['label' => 'Bot provider', 'status' => $botReadiness['status'], 'message' => $botReadiness['message']], ['label' => 'Akismet', 'status' => $akismetReadiness['status'], 'message' => $akismetReadiness['message']], ['label' => 'Rate limits', 'status' => $rateLimitStatus, 'message' => 'Laravel limiters use configured safe policies.'], ['label' => 'Admin access', 'status' => $adminAccessReadiness['owner_last_admin_protection'], 'message' => 'Owner and last admin protections remain enforced.']] as $item)
            <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-extrabold uppercase text-stone-500">{{ $item['label'] }}</p>
                    <x-security.status-badge :status="$item['status']" />
                </div>
                <p class="mt-3 text-sm font-semibold leading-6 text-stone-600">{{ $item['message'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <div class="min-w-0 space-y-6">
            <x-security.rate-limit-panel
                :policies="$rateLimitPolicies"
                :strategies="$rateLimitStrategies"
                :readiness="$rateLimitReadiness"
                :status="$rateLimitStatus"
                :can-update="$canManageRateLimits"
            />

            <x-security.admin-access-card
                :settings="$adminAccess"
                :readiness="$adminAccessReadiness"
                :can-update="$canManageAdminSecurity"
            />

            <x-security.provider-card
                :settings="$botSettings"
                :providers="$providers"
                :forms="$protectedForms"
                :fail-modes="$failModes"
                :can-update="$canUpdateSecurity"
                :can-reveal="$canRevealSecrets"
            />

            <x-security.akismet-panel
                :settings="$akismetSettings"
                :can-update="$canUpdateSecurity"
                :can-reveal="$canRevealSecrets"
            />
        </div>

        <aside class="min-w-0 space-y-6">
            <x-security.ip-list-panel
                :settings="$ipAccess"
                :readiness="$ipAccessReadiness"
                :can-update="$canManageAdminSecurity"
            />

            <x-security.failed-login-summary
                :summary="$failedLoginSummary"
                :can-view="$canViewFailedLogins"
            />

            <x-security.force-logout-warning :can-force="$canForceLogout" />

            <x-security.test-connection-panel
                target="bot_protection"
                :status="$botReadiness['status']"
                :message="$botReadiness['message']"
                :history="$botSettings['test_history']"
                :can-test="$canUpdateSecurity"
            />

            <x-security.test-connection-panel
                target="akismet"
                :status="$akismetReadiness['status']"
                :message="$akismetReadiness['message']"
                :history="$akismetSettings['test_history']"
                :can-test="$canUpdateSecurity"
            />

            <x-admin.card title="Admin lockout protection" description="Bad provider settings never replace CSRF or core login access controls.">
                <div class="space-y-3 text-sm leading-6 text-stone-600">
                    <p>Deactivating a provider keeps its configuration so recovery does not require retyping keys.</p>
                    <p>Fail mode can be set to log only while validating production traffic.</p>
                    <p>Secret values remain encrypted at rest and masked by default.</p>
                    <p>Suspended users and normal users cannot enter the admin shell.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
