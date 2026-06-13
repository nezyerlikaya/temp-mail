@props(['versions', 'selectedTheme', 'canRollback' => false])

<x-admin.card title="Version History" description="Published appearance versions can be restored safely as a new version.">
    @if ($versions->isEmpty())
        <p class="rounded-md border border-stone-200 bg-stone-50 p-3 text-sm font-semibold text-stone-600">No published appearance versions yet.</p>
    @else
        <div class="space-y-4">
            @foreach ($versions as $version)
                <article class="rounded-md border border-stone-200 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold text-stone-950">Version {{ $version->version_number }}</p>
                            <p class="mt-1 text-xs font-bold text-stone-500">{{ $version->created_at->format('M j, Y H:i') }} · {{ $version->publisher?->email ?? 'System' }}</p>
                        </div>
                        <x-appearance.contrast-badge :status="($version->contrast_report['summary']['publishable'] ?? false) ? 'pass' : 'fail'" />
                    </div>
                    <form method="POST" action="{{ route('admin.appearance-studio.rollback') }}" class="mt-3" x-data="{ submitting: false }" x-on:submit="submitting = true">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
                        <input type="hidden" name="version_id" value="{{ $version->id }}">
                        <x-appearance.rollback-warning :version="$version" :selected-theme="$selectedTheme" />
                        <button type="submit" @disabled(! $canRollback) class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-md border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700 hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20 disabled:cursor-not-allowed disabled:opacity-60">Rollback to version</button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</x-admin.card>
