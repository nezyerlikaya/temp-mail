<x-admin.layout title="Operations Overview" :user="$adminUser">
    <div
        x-data="dashboardLive({
            endpoint: @js(route('admin.dashboard.live-metrics')),
            initial: @js($livePayload)
        })"
    >
        <x-admin.page-header
            eyebrow="Workspace"
            title="Operations Overview"
            description="A cached command view of inbox activity, infrastructure health, security attention, and system readiness."
        >
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <x-dashboard.live-status />
                    <x-dashboard.refresh-interval-control />
                    <x-dashboard.refresh-button />
                </div>
            </x-slot:actions>
        </x-admin.page-header>

        <x-dashboard.connection-warning />
        <x-dashboard.stale-warning />
        <x-dashboard.critical-alert-strip :alerts="$livePayload['alerts']" />
        <x-dashboard.alert-strip :alerts="$summary['alerts']" />

        <section aria-labelledby="operations-metrics-title" class="mb-6">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 id="operations-metrics-title" class="text-base font-extrabold text-stone-950">Operational metrics</h2>
                <p class="text-xs font-bold text-stone-500">Cached lightweight summary</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($livePayload['metrics'] as $metric)
                    <x-dashboard.live-metric-card :metric="$metric" />
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
            <div class="space-y-6">
                <x-dashboard.health-summary-card :items="$summary['health']" />
                <x-dashboard.activity-feed :items="$summary['activity']" />
            </div>

            <aside class="space-y-6">
                <x-dashboard.attention-queue :alerts="$summary['alerts']" />
                <x-dashboard.quick-actions :actions="$summary['quick_actions']" />
            </aside>
        </div>
    </div>
</x-admin.layout>
