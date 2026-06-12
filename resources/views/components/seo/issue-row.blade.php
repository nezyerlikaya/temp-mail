@props(['issue'])

@php($record = $issue['record'] ?? null)

<div class="grid gap-3 border-b border-stone-100 p-4 last:border-b-0 md:grid-cols-[120px_minmax(0,1fr)_auto] md:items-center">
    <x-seo.severity-badge :severity="$issue['severity']" />
    <div class="min-w-0">
        <p class="text-sm font-extrabold text-stone-950">{{ $issue['title'] }}</p>
        <p class="mt-1 text-sm text-stone-600">{{ $issue['message'] }}</p>
        @if ($record)
            <p class="mt-1 truncate text-xs font-bold text-stone-500">{{ $record->locale?->locale ?? 'global' }} · {{ str($record->target_type)->replace('_', ' ')->headline() }} · {{ $record->target_key }}</p>
        @endif
    </div>
    @if ($record)
        <a href="{{ route('admin.seo-growth-center.records.edit', $record) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Fix</a>
    @endif
</div>
