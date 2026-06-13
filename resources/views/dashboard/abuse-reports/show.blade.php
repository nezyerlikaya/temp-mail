<x-admin.layout :user="auth()->user()" title="Abuse Case {{ $report->case_reference }}">
    <x-admin.page-header eyebrow="Abuse Reports" :title="$report->case_reference" description="Human-submitted case review. No automatic blocking or deletion occurs from this screen." />
    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-error-summary />
    <x-abuse.case-detail :report="$report" :can-view-sensitive="$canViewSensitive" />
    <section class="mt-5 grid gap-5 lg:grid-cols-2">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm"><x-abuse.assignment-control :report="$report" :administrators="$administrators" :can-assign="$canAssign" /></div>
        @if ($canUpdateStatus)<form method="POST" action="{{ route('admin.abuse-reports.status', $report) }}" class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">@csrf @method('PUT')<label for="case-status" class="block text-sm font-bold text-stone-900">Case status</label><select id="case-status" name="status" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">@foreach (['new','reviewing','awaiting_information','resolved','rejected','archived'] as $status)<option value="{{ $status }}" @selected($report->status === $status)>{{ str($status)->replace('_', ' ')->headline() }}</option>@endforeach</select><button class="mt-3 inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Update status</button></form>@endif
    </section>
</x-admin.layout>
