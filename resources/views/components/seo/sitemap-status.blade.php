@props(['statuses'])

<x-admin.card title="Sitemap readiness" description="Language, page, blog, and media sitemap readiness before generation.">
    <div class="space-y-3">
        @foreach ($statuses as $status)
            <div class="flex items-center justify-between gap-3 rounded-lg border border-stone-200 p-3">
                <div>
                    <p class="text-sm font-extrabold text-stone-950">{{ $status['label'] }}</p>
                    <p class="mt-1 text-xs font-bold text-stone-500">{{ $status['ready'] }}/{{ $status['total'] }} ready</p>
                </div>
                <x-seo.severity-badge :severity="$status['state'] === 'ready' ? 'ready' : ($status['state'] === 'empty' ? 'notice' : 'warning')" />
            </div>
        @endforeach
    </div>
</x-admin.card>
