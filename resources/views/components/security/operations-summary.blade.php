@props(['metrics'])

<section aria-labelledby="security-operations-title">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase text-teal-700">Security operations</p>
            <h2 id="security-operations-title" class="mt-1 text-xl font-extrabold text-stone-950">Abuse monitoring overview</h2>
        </div>
        <p class="max-w-2xl text-sm leading-6 text-stone-600">Operational signals contain safe metadata only. Message bodies, credentials, provider secrets, and session tokens are excluded.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
        <x-security.metric-card label="Open alerts" :value="$metrics['open_alerts']" :tone="$metrics['open_alerts'] > 0 ? 'warning' : 'neutral'" />
        <x-security.metric-card label="Critical alerts" :value="$metrics['critical_alerts']" :tone="$metrics['critical_alerts'] > 0 ? 'critical' : 'neutral'" />
        <x-security.metric-card label="Bot challenges" :value="$metrics['bot_challenges']" />
        <x-security.metric-card label="Spam blocked" :value="$metrics['spam_blocked']" tone="positive" />
        <x-security.metric-card label="Failed logins" :value="$metrics['failed_logins']" :tone="$metrics['failed_logins'] > 0 ? 'warning' : 'neutral'" />
        <x-security.metric-card label="Rate limited" :value="$metrics['rate_limited_requests']" />
    </div>
</section>
