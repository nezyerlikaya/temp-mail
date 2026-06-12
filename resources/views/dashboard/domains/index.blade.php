<x-admin.layout title="Domains" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Mail Infrastructure"
        title="Domain Operations Center"
        description="Manage receiving domains, DNS readiness, catch-all state, and public mailbox availability."
    >
        <x-slot:actions>
            @if ($canCreateDomain)
                <a href="{{ route('admin.domains.create') }}" class="inline-flex min-h-10 items-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Add domain</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    @if ($errors->any())
        <x-admin.alert variant="danger" class="mb-6" title="Domain operation needs attention">
            {{ $errors->first() }}
        </x-admin.alert>
    @endif

    <div class="mb-6 space-y-5">
        <x-domains.health-summary :summary="$summary" :readiness="$readinessSummary" />
        <x-domains.filter-bar :filters="$filters" :statuses="$statuses" />
    </div>

    <x-admin.card title="Receiving domains" description="Operational queue for public availability and DNS readiness.">
        @if ($domains->count())
            <div class="-mx-5 -my-4 sm:-mx-6">
                @foreach ($domains as $domain)
                    <x-domains.domain-row
                        :domain="$domain"
                        :can-change-status="$canChangeStatus"
                        :can-set-default="$canSetDefault"
                        :can-run-dns-checks="$canRunDnsChecks"
                    />
                @endforeach
            </div>
            <div class="mt-5">{{ $domains->links() }}</div>
        @else
            <x-domains.empty-state />
        @endif
    </x-admin.card>
</x-admin.layout>
