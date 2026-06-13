@props(['report', 'canViewSensitive'])
<div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
    <section class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-2"><x-abuse.priority-badge :priority="$report->priority" /><x-abuse.status-badge :status="$report->status" /><x-abuse.type-badge :type="$report->report_type" /></div>
        <h2 class="mt-4 text-xl font-extrabold text-stone-950">{{ $report->subject }}</h2><p class="mt-2 font-mono text-xs font-bold text-stone-500">{{ $report->case_reference }}</p>
        <div class="mt-6 whitespace-pre-line text-sm leading-7 text-stone-700">{{ $report->description }}</div>
        @if ($report->related_url)<a href="{{ $report->related_url }}" rel="noopener noreferrer" class="mt-5 block break-all text-sm font-bold text-teal-800 underline">{{ $report->related_url }}</a>@endif
    </section>
    <aside class="space-y-4"><x-abuse.reporter-summary :report="$report" :can-view-sensitive="$canViewSensitive" /><x-abuse.evidence-summary :report="$report" /></aside>
</div>
