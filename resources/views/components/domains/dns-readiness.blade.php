@props(['domain', 'expectedRecords' => []])

@php
    $checks = $domain?->dns_checks ?? [];
    $records = $checks ?: collect($expectedRecords)->map(fn ($record) => [
        'type' => $record['type'],
        'host' => $record['host'],
        'expected' => $record['value'],
        'detected' => [],
        'status' => 'draft',
        'guidance' => 'Run a DNS check to detect public records.',
    ])->all();
@endphp

<x-admin.card title="DNS readiness" description="Expected public records and last detected values. No provider credentials are stored here.">
    @if ($domain?->last_checked_at)
        <p class="mb-4 text-sm font-semibold text-stone-600">Last checked {{ $domain->last_checked_at->diffForHumans() }}</p>
    @endif

    <div class="space-y-3">
        @foreach ($records as $key => $record)
            <x-domains.dns-record-row :record="$record" :label="str($key)->replace('_', ' ')->headline()" />
        @endforeach
    </div>
</x-admin.card>
