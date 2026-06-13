<x-admin.layout :user="auth()->user()" title="Abuse Case {{ $report->case_reference }}">
    <x-admin.page-header eyebrow="Abuse Reports" :title="$report->case_reference" description="Human-submitted case review. No automatic blocking or deletion occurs from this screen." />
    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-error-summary />
    <x-abuse.case-detail :report="$report" :can-view-sensitive="$canViewSensitive" />
    <div class="mt-5"><x-abuse.resolution-summary :report="$report" /></div>
    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm"><x-abuse.assignment-control :report="$report" :administrators="$administrators" :can-assign="$canAssign" /></div>
        @if ($canUpdateStatus && !in_array($report->status, ['resolved', 'rejected', 'archived'], true))<form method="POST" action="{{ route('admin.abuse-reports.status', $report) }}" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">@csrf @method('PUT')<label for="case-status" class="block text-sm font-bold text-stone-900">Review status</label><select id="case-status" name="status" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">@foreach (['new','reviewing','awaiting_information'] as $status)<option value="{{ $status }}" @selected($report->status === $status)>{{ str($status)->replace('_', ' ')->headline() }}</option>@endforeach</select><p class="mt-2 text-xs text-stone-500">Resolved, rejected, and archived states require their dedicated reasoned actions.</p><button class="mt-3 inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Update review status</button></form>@endif
    </section>
    <section class="mt-5 grid gap-5 xl:grid-cols-2"><x-abuse.internal-note-form :report="$report" :notes="$report->notes" :can-add="$canAddNotes" /><x-abuse.evidence-panel :report="$report" :media-assets="$mediaAssets" :can-view="$canViewEvidence" :can-manage="$canManageEvidence" /></section>
    <section class="mt-5 grid gap-5 xl:grid-cols-[1.15fr_.85fr]"><x-abuse.resolution-panel :report="$report" :readiness="$reporterReadiness" :can-resolve="$canResolve" /><x-abuse.blocklist-action-panel :report="$report" :can-execute="$canExecuteActions" /></section>
    <section class="mt-5 grid gap-5 lg:grid-cols-2"><x-abuse.reopen-warning :report="$report" :can-reopen="$canReopenArchive" /><x-abuse.archive-warning :report="$report" :can-archive="$canReopenArchive" /></section>
    <div class="mt-5"><x-abuse.case-timeline :events="$timeline" /></div>
</x-admin.layout>
