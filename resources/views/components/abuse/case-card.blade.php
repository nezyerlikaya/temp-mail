@props(['report', 'canViewSensitive'])
<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2"><x-abuse.priority-badge :priority="$report->priority" /><x-abuse.status-badge :status="$report->status" /><x-abuse.type-badge :type="$report->report_type" /></div>
            <a href="{{ route('admin.abuse-reports.show', $report) }}" class="mt-3 block text-base font-extrabold text-stone-950 hover:text-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $report->subject }}</a>
            <p class="mt-1 font-mono text-xs font-bold text-stone-500">{{ $report->case_reference }}</p>
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $report->description_excerpt }}</p>
        </div>
        <dl class="grid shrink-0 gap-3 text-xs sm:grid-cols-3 lg:w-64 lg:grid-cols-1">
            <div><dt class="font-extrabold uppercase text-stone-500">Assignee</dt><dd class="mt-1 font-bold text-stone-800">{{ $report->assignee?->name ?? 'Unassigned' }}</dd></div>
            <div><dt class="font-extrabold uppercase text-stone-500">Submitted</dt><dd class="mt-1 font-bold text-stone-800">{{ $report->created_at?->format('M j, Y H:i') }}</dd></div>
            <div><dt class="font-extrabold uppercase text-stone-500">Reporter</dt><dd class="mt-1 font-bold text-stone-800">{{ $canViewSensitive ? $report->reporter_email : 'Protected' }}</dd></div>
        </dl>
    </div>
</article>
