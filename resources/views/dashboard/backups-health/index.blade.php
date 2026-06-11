<x-admin.layout title="Backups & Health" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="Backups & Health"
        description="Create production-safe manual backups for database snapshots, storage uploads, and secret-free config references."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Active" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    @if (session('warning'))
        <x-admin.alert variant="warning" class="mb-6">{{ session('warning') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Backup summary">
        @foreach ([
            ['label' => 'Backups', 'value' => $summary['total']],
            ['label' => 'Completed', 'value' => $summary['completed']],
            ['label' => 'Failed', 'value' => $summary['failed']],
            ['label' => 'Stored size', 'value' => number_format($summary['total_size'] / 1024, 2).' KB'],
        ] as $metric)
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </section>

    <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section class="min-w-0" aria-labelledby="backup-list-title">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="backup-list-title" class="text-lg font-extrabold text-stone-950">Backup list</h2>
                    <p class="mt-1 text-sm text-stone-600">Read-only manifest history. Download and delete are owner-only.</p>
                </div>
                <p class="text-sm font-bold text-stone-500">{{ $backups->count() }} records</p>
            </div>

            @if ($backups->count() > 0)
                <x-system.backup-list :backups="$backups" :integrity="$integrity" :can-download="$canDownload" :can-delete="$canDelete" />
            @else
                <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
                    <x-system.empty-state title="No backups yet" description="Create a manual backup before updates or infrastructure changes. Restore is intentionally not implemented in MVP." />
                </div>
            @endif
        </section>

        <aside class="min-w-0 space-y-6">
            <x-system.disk-space-card :disk-space="$diskSpace" />
            <x-system.backup-action-panel :can-create="$canCreate" :summary="$summary" />
        </aside>
    </div>
</x-admin.layout>
