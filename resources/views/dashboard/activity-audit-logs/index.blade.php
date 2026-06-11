<x-admin.layout title="Activity & Audit Logs" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Trust"
        title="Activity & Audit Logs"
        description="Read-only activity and compliance feed showing who changed what, when, from where, and in which module."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Active" />
        </x-slot:actions>
    </x-admin.page-header>

    <x-error-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Audit summary">
        @foreach ([
            ['label' => 'Events', 'value' => $summary['total']],
            ['label' => 'Critical', 'value' => $summary['critical']],
            ['label' => 'Warnings', 'value' => $summary['warnings']],
            ['label' => 'Known actors', 'value' => $summary['actors']],
        ] as $metric)
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ number_format($metric['value']) }}</p>
            </div>
        @endforeach
    </section>

    <x-admin.card title="Search and filters" description="Filter audit activity without changing the underlying records. Secret fields are masked before storage.">
        <x-audit.filter-bar :filters="$filters" :options="$filterOptions" />
    </x-admin.card>

    <section class="mt-6" aria-labelledby="audit-feed-title">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="audit-feed-title" class="text-lg font-extrabold text-stone-950">Audit feed</h2>
                <p class="mt-1 text-sm text-stone-600">System actors, failed-login readiness, target links, and correlation IDs are captured for later compliance layers.</p>
            </div>
            <p class="text-sm font-bold text-stone-500">{{ $events->total() }} records</p>
        </div>

        @if ($events->count() > 0)
            <x-audit.feed :events="$events" />
            <div class="mt-5">
                {{ $events->links() }}
            </div>
        @else
            <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
                <x-audit.empty-state />
            </div>
        @endif
    </section>
</x-admin.layout>
