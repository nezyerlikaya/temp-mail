@props(['summary'])

<x-admin.card title="Search health summary" description="Foundation signals for metadata coverage and crawl intent.">
    <div class="space-y-4">
        <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-extrabold text-stone-950">Coverage</p>
                <p class="text-lg font-extrabold text-stone-950">{{ $summary['coverage'] }}%</p>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-200">
                <div class="h-full rounded-full bg-teal-700" style="width: {{ min(100, max(0, $summary['coverage'])) }}%"></div>
            </div>
        </div>

        @if (($summary['issues'] ?? []) !== [])
            <x-admin.alert variant="warning" title="Attention queue">
                <ul class="space-y-1">
                    @foreach ($summary['issues'] as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </x-admin.alert>
        @else
            <x-admin.alert variant="success" title="Foundation ready">
                SEO record coverage has no foundation warnings.
            </x-admin.alert>
        @endif

        <dl class="grid gap-3 text-sm">
            <div class="flex items-center justify-between rounded-lg border border-stone-200 p-3">
                <dt class="font-bold text-stone-600">Noindex records</dt>
                <dd class="font-extrabold text-stone-950">{{ $summary['noindex'] }}</dd>
            </div>
            <div class="flex items-center justify-between rounded-lg border border-stone-200 p-3">
                <dt class="font-bold text-stone-600">Missing metadata</dt>
                <dd class="font-extrabold text-stone-950">{{ $summary['missing_metadata'] }}</dd>
            </div>
        </dl>
    </div>
</x-admin.card>
