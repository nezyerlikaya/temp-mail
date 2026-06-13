@props(['report'])
@if ($report->resolved_at)
<section class="rounded-lg border border-emerald-200 bg-emerald-50 p-5">
    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-extrabold uppercase text-emerald-800">Resolution summary</p><h2 class="mt-1 text-base font-extrabold text-emerald-950">{{ str($report->resolution_outcome)->replace('_', ' ')->headline() }}</h2></div><time class="text-xs font-semibold text-emerald-800">{{ $report->resolved_at->format('M j, Y H:i') }}</time></div>
    <p class="mt-3 text-sm leading-6 text-emerald-950">{{ $report->resolution_summary ?: $report->resolution_reason }}</p><p class="mt-2 text-xs font-semibold text-emerald-800">Resolved by {{ $report->resolver?->name ?? 'Former administrator' }} · Retention review {{ $report->retention_review_at?->format('M j, Y') ?? 'not scheduled' }}</p>
</section>
@endif
