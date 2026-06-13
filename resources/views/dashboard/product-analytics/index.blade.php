<x-admin.layout title="Product Analytics" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Workspace"
        title="Product Analytics"
        description="Privacy-friendly operational analytics built from daily aggregates, not private message content."
    />

    @if($errors->any())
        <x-admin.alert variant="danger" class="mb-6" role="alert">
            <p class="font-extrabold">Review the analytics filters.</p>
            <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </x-admin.alert>
    @endif

    <div class="space-y-6">
        <x-analytics.privacy-note />
        <x-analytics.stale-warning :freshness="$dashboard['freshness']" />
        <x-analytics.filter-bar :filters="$filters" :presets="$presets" :locales="$locales" :domains="$domains" />

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @foreach($dashboard['kpis'] as $metric)
                <x-analytics.metric-card :label="$metric['label']" :value="$metric['value']" :detail="$metric['detail']" />
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-6">
                <x-analytics.section title="Mailbox Activity" description="Aggregate mailbox lifecycle and email ingestion trends.">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-analytics.trend-chart title="Mailboxes created" :points="$dashboard['sections']['mailbox']['mailboxes_created']" />
                        <x-analytics.trend-chart title="Emails received" :points="$dashboard['sections']['mailbox']['emails_received']" />
                        <x-analytics.trend-chart title="Created vs expired" :points="$dashboard['sections']['mailbox']['inbox_lifecycle']" />
                    </div>
                </x-analytics.section>

                <x-analytics.section title="Domain Usage" description="Domain-level mailbox and email aggregate usage.">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-analytics.top-list title="Mailbox count per domain" :items="$dashboard['sections']['domains']['mailboxes']" />
                        <x-analytics.top-list title="Email count per domain" :items="$dashboard['sections']['domains']['emails']" />
                        <x-analytics.top-list title="Domain health readiness" :items="$dashboard['sections']['domains']['health']" />
                    </div>
                </x-analytics.section>

                <x-analytics.section title="Content Performance" description="Blog and language content signals from aggregate events.">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-analytics.trend-chart title="Blog views" :points="$dashboard['sections']['content']['blog_views']" />
                        <x-analytics.top-list title="Top language pages" :items="$dashboard['sections']['content']['top_languages']" />
                        <x-analytics.empty-state title="SEO landing pages readiness" description="{{ $dashboard['sections']['content']['seo_landing_pages'] }}. Landing-page attribution arrives with later public theme analytics." />
                    </div>
                </x-analytics.section>
            </div>

            <aside class="space-y-6">
                <x-analytics.export-panel :can-export="$canExport" :filters="$filters" />

                <x-analytics.section title="User Conversion" description="Lightweight conversion readiness without cohort tracking.">
                    <div class="space-y-3">
                        <x-analytics.metric-card label="Guest to registered" :value="$dashboard['sections']['conversion']['guest_to_registered']" detail="Registrations over mailbox creation" />
                        <x-analytics.metric-card label="Registered to premium" :value="$dashboard['sections']['conversion']['registered_to_premium']" detail="Premium grants over registrations" />
                        <x-analytics.metric-card label="Premium expiring" :value="$dashboard['sections']['conversion']['premium_expiring']" detail="Membership expiration readiness" />
                    </div>
                </x-analytics.section>

                <x-analytics.section title="Security & Abuse Signals" description="Aggregate safety signals and readiness counters.">
                    <div class="space-y-3">
                        <x-analytics.metric-card label="Failed logins" :value="$dashboard['sections']['security']['failed_logins']" detail="Security signal count" />
                        <x-analytics.metric-card label="Rate limited" :value="$dashboard['sections']['security']['rate_limited']" detail="Aggregate rate-limit events" />
                        <x-analytics.metric-card label="Blocked comments" :value="$dashboard['sections']['security']['blocked_comments']" detail="Spam blocked signals" />
                        <x-analytics.metric-card label="Spam comments" :value="$dashboard['sections']['security']['spam_comments']" detail="Suspicious comment signals" />
                    </div>
                </x-analytics.section>
            </aside>
        </div>
    </div>
</x-admin.layout>
