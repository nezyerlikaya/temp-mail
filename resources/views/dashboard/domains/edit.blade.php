<x-admin.layout title="Edit Domain" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Mail Infrastructure"
        title="{{ $domain->domain_name }}"
        description="Review availability, DNS records, and remediation guidance for this receiving domain."
    >
        <x-slot:actions>
            @if ($canRunDnsChecks)
                <form method="POST" action="{{ route('admin.domains.dns-check', $domain) }}">
                    @csrf
                    <button class="inline-flex min-h-10 items-center rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Run DNS Check</button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    @if ($errors->any())
        <x-admin.alert variant="danger" class="mb-6" title="Domain settings need attention">
            {{ $errors->first() }}
        </x-admin.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_440px]">
        <div class="space-y-6">
            <x-domains.domain-editor :domain="$domain" :statuses="$statuses" :can-update="$canUpdateDomain" />
            <x-domains.dns-readiness :domain="$domain" :expected-records="$expectedRecords" />
        </div>

        <aside class="space-y-6">
            <x-domains.domain-card :domain="$domain" />

            <x-admin.card title="Expected DNS ownership" description="Publish this TXT value when domain control verification is required.">
                @foreach ($expectedRecords as $key => $record)
                    @if ($key === 'ownership')
                        <x-domains.dns-record-row :record="['type' => $record['type'], 'host' => $record['host'], 'expected' => $record['value'], 'detected' => [], 'status' => 'draft', 'guidance' => 'This value is derived from the app key and domain name. It is not a secret credential.']" label="Ownership TXT" />
                    @endif
                @endforeach
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
