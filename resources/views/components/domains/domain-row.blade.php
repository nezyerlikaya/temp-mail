@props(['domain', 'canChangeStatus' => false, 'canSetDefault' => false, 'canRunDnsChecks' => false])

<div class="grid gap-4 border-b border-stone-200 px-5 py-4 last:border-b-0 lg:grid-cols-[minmax(0,1.3fr)_1fr_auto] lg:items-center">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.domains.edit', $domain) }}" class="truncate text-sm font-extrabold text-stone-950 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $domain->domain_name }}</a>
            <x-domains.default-badge :default="$domain->is_default" />
            <x-domains.status-badge :status="$domain->status" />
        </div>
        <p class="mt-1 text-sm font-semibold text-stone-600">{{ $domain->display_name }}</p>
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs font-bold text-stone-600 sm:grid-cols-4">
        <span>{{ $domain->is_active ? 'Active' : 'Passive' }}</span>
        <span>{{ $domain->is_public ? 'Public' : 'Private' }}</span>
        <span>{{ $domain->catch_all_ready ? 'Catch-all ready' : 'Catch-all pending' }}</span>
        <span>{{ $domain->last_checked_at ? $domain->last_checked_at->diffForHumans() : 'Not checked' }}</span>
    </div>
    <div class="flex flex-wrap gap-2 lg:justify-end">
        @if (! $domain->is_default)
            <form method="POST" action="{{ route('admin.domains.default', $domain) }}">
                @csrf
                <button @disabled(! $canSetDefault || ! $domain->is_active) class="inline-flex min-h-9 items-center rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-700 hover:bg-stone-50 disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">Set default</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.domains.status', $domain) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status_action" value="{{ $domain->is_active ? 'deactivate' : 'activate' }}">
            <button @disabled(! $canChangeStatus) class="inline-flex min-h-9 items-center rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-700 hover:bg-stone-50 disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">{{ $domain->is_active ? 'Deactivate' : 'Activate' }}</button>
        </form>
        <form method="POST" action="{{ route('admin.domains.dns-check', $domain) }}">
            @csrf
            <button @disabled(! $canRunDnsChecks) class="inline-flex min-h-9 items-center rounded-md bg-stone-950 px-3 text-xs font-extrabold text-white hover:bg-stone-800 disabled:cursor-not-allowed disabled:bg-stone-400">Run DNS</button>
        </form>
    </div>
</div>
