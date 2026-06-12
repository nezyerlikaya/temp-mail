@props(['connection', 'canManage' => false, 'canTest' => false, 'canToggle' => false, 'extension'])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="truncate text-base font-extrabold text-stone-950">{{ $connection->name }}</h2>
                <x-mail.connection-status-badge :status="$connection->status" />
            </div>
            <p class="mt-1 text-sm font-semibold text-stone-600">{{ $connection->domain->domain_name }}</p>
        </div>
        <span class="inline-flex min-h-7 items-center rounded-md border px-2.5 text-xs font-extrabold {{ $connection->is_active ? 'border-teal-200 bg-teal-50 text-teal-800' : 'border-stone-200 bg-stone-100 text-stone-600' }}">{{ $connection->is_active ? 'Active' : 'Passive' }}</span>
    </div>

    <dl class="mt-5 grid grid-cols-2 gap-4 border-y border-stone-200 py-4 text-sm">
        <div>
            <dt class="text-xs font-bold text-stone-500">Endpoint</dt>
            <dd class="mt-1 break-all font-extrabold text-stone-900">{{ $connection->host }}:{{ $connection->port }}</dd>
        </div>
        <div>
            <dt class="text-xs font-bold text-stone-500">Security</dt>
            <dd class="mt-1 font-extrabold uppercase text-stone-900">{{ $connection->encryption }} · {{ $connection->validate_certificate ? 'cert verified' : 'cert validation off' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-bold text-stone-500">Mailbox</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ $connection->mailbox }}</dd>
        </div>
        <div>
            <dt class="text-xs font-bold text-stone-500">Last test</dt>
            <dd class="mt-1 font-extrabold text-stone-900">{{ $connection->last_tested_at?->diffForHumans() ?? 'Never' }}</dd>
        </div>
    </dl>

    <div class="mt-4 flex flex-wrap gap-2">
        @if ($canManage)
            <a href="{{ route('admin.imap-smtp.edit', $connection) }}" class="inline-flex min-h-9 items-center gap-2 rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <i data-lucide="settings-2" class="size-4" aria-hidden="true"></i> Configure
            </a>
        @endif
        <form method="POST" action="{{ route('admin.imap-smtp.test', $connection) }}">
            @csrf
            <button @disabled(! $canTest || ! $extension['ready']) class="inline-flex min-h-9 items-center gap-2 rounded-md bg-stone-950 px-3 text-xs font-extrabold text-white hover:bg-stone-800 disabled:cursor-not-allowed disabled:bg-stone-400">
                <i data-lucide="plug-zap" class="size-4" aria-hidden="true"></i> Test
            </button>
        </form>
        <form method="POST" action="{{ route('admin.imap-smtp.status', $connection) }}">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status_action" value="{{ $connection->is_active ? 'deactivate' : 'activate' }}">
            <button @disabled(! $canToggle) class="inline-flex min-h-9 items-center rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-700 hover:bg-stone-50 disabled:cursor-not-allowed disabled:bg-stone-100 disabled:text-stone-400">{{ $connection->is_active ? 'Deactivate' : 'Activate' }}</button>
        </form>
    </div>
</article>
