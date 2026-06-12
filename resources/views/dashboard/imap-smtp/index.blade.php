<x-admin.layout title="IMAP / Inbound Mail" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Mail Infrastructure"
        title="Inbound Mail Operations"
        description="Connect receiving domains to IMAP-compatible providers and monitor safe readiness checks."
    >
        <x-slot:actions>
            @if ($canManage && $domains->isNotEmpty())
                <a href="{{ route('admin.imap-smtp.create') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <i data-lucide="plus" class="size-4" aria-hidden="true"></i> Add connection
                </a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    @if (session('error'))<x-admin.alert variant="danger" class="mb-6">{{ session('error') }}</x-admin.alert>@endif
    @if ($errors->any())<x-admin.alert variant="danger" class="mb-6" title="Inbound mail operation needs attention">{{ $errors->first() }}</x-admin.alert>@endif

    <div class="space-y-6">
        <x-mail.extension-warning :extension="$extension" />

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([['Connections', $summary['total']], ['Active', $summary['active']], ['Connected', $summary['connected']], ['Failed', $summary['failed']], ['Not tested', $summary['untested']]] as [$label, $value])
                <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold text-stone-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-black text-stone-950">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.imap-smtp.index') }}" class="grid gap-3 rounded-lg border border-stone-200 bg-white p-4 shadow-sm lg:grid-cols-[minmax(0,1fr)_220px_220px_auto]">
            <label>
                <span class="sr-only">Search connections</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search connection, host, user, or domain" class="min-h-11 w-full rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
            <label>
                <span class="sr-only">Filter by status</span>
                <select name="status" class="min-h-11 w-full rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="all">All statuses</option>
                    @foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach
                </select>
            </label>
            <label>
                <span class="sr-only">Filter by domain</span>
                <select name="domain_id" class="min-h-11 w-full rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="all">All domains</option>
                    @foreach ($domains as $domain)<option value="{{ $domain->id }}" @selected($filters['domain_id'] === (string) $domain->id)>{{ $domain->domain_name }}</option>@endforeach
                </select>
            </label>
            <button class="inline-flex min-h-11 items-center justify-center rounded-md border border-stone-300 bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Filter</button>
        </form>

        @if ($connections->count())
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($connections as $connection)
                    <x-mail.inbound-connection-card :connection="$connection" :can-manage="$canManage" :can-test="$canTest" :can-toggle="$canToggle" :extension="$extension" />
                @endforeach
            </div>
            <div>{{ $connections->links() }}</div>
        @else
            <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
                <x-mail.empty-state :can-manage="$canManage" :has-domains="$domains->isNotEmpty()" />
            </div>
        @endif
    </div>
</x-admin.layout>
