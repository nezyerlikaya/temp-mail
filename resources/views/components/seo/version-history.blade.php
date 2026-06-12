@props(['versions', 'canRollback' => false])

<x-admin.card title="Version history" description="Rollback readiness snapshots created before SEO record updates.">
    @if ($versions->count() > 0)
        <div class="space-y-3">
            @foreach ($versions as $version)
                <div class="rounded-lg border border-stone-200 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold text-stone-950">{{ $version->record?->target_key ?? 'SEO record' }}</p>
                            <p class="mt-1 text-xs font-bold text-stone-500">{{ $version->created_at?->diffForHumans() }} · {{ $version->creator?->name ?? 'System' }}</p>
                        </div>
                        @if ($canRollback)
                            <form method="POST" action="{{ route('admin.seo-growth-center.versions.rollback', $version) }}">
                                @csrf
                                <button class="inline-flex min-h-9 items-center justify-center rounded-lg border border-stone-300 px-3 text-xs font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Rollback</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm font-bold text-stone-600">No SEO snapshots yet.</p>
    @endif
</x-admin.card>
