@props(['domain'])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <x-domains.status-badge :status="$domain->status" />
                <x-domains.default-badge :default="$domain->is_default" />
            </div>
            <h3 class="mt-3 truncate text-base font-extrabold text-stone-950">{{ $domain->domain_name }}</h3>
            <p class="mt-1 text-sm font-semibold text-stone-600">{{ $domain->display_name }}</p>
        </div>
    </div>
    <dl class="mt-4 grid gap-3 text-sm">
        <div class="flex justify-between gap-3"><dt class="font-bold text-stone-500">Availability</dt><dd class="font-extrabold text-stone-900">{{ $domain->is_public ? 'Public' : 'Private' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="font-bold text-stone-500">Catch-all</dt><dd class="font-extrabold text-stone-900">{{ $domain->catch_all_ready ? 'Ready' : 'Pending' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="font-bold text-stone-500">Last check</dt><dd class="font-extrabold text-stone-900">{{ $domain->last_checked_at ? $domain->last_checked_at->diffForHumans() : 'Not checked' }}</dd></div>
    </dl>
</article>
