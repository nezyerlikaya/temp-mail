@props(['integration', 'environment', 'history', 'canTest' => false])

<section class="mb-4 rounded-lg border border-stone-200 bg-stone-50 p-4" aria-labelledby="integration-test-panel">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 id="integration-test-panel" class="text-sm font-extrabold text-stone-950">Connection test</h3>
            <p class="mt-1 text-xs font-semibold text-stone-600">Manual readiness probe for {{ str($environment)->headline() }}. No destructive or billable action is performed.</p>
        </div>
        <x-integrations.retry-button :integration="$integration" :environment="$environment" :can-test="$canTest" />
    </div>

    <div class="mt-4">
        <x-integrations.test-status :status="$integration['connection_status']" :last-tested-at="$integration['last_tested_at']" />
    </div>

    <x-integrations.health-history :history="$history" />
</section>
