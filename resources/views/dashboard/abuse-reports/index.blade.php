<x-admin.layout :user="auth()->user()" title="Abuse Reports">
    <x-admin.page-header eyebrow="Trust" title="Abuse Reports" description="Review human-submitted complaints and investigation cases separately from automated security signals." />
    @if (session('status'))<x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>@endif
    <x-error-summary />
    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Abuse case summary">
        @foreach (['New' => $summary['new'], 'Reviewing' => $summary['reviewing'], 'Awaiting information' => $summary['awaiting_information'], 'Critical open' => $summary['critical'], 'Unassigned' => $summary['unassigned']] as $label => $value)<div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm"><p class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold text-stone-950">{{ $value }}</p></div>@endforeach
    </section>
    <div class="space-y-5"><x-abuse.filter-bar :filters="$filters" :administrators="$administrators" />@if ($reports->count())<x-abuse.case-queue :reports="$reports" :can-view-sensitive="$canViewSensitive" /><x-admin.pagination :paginator="$reports" />@else<x-abuse.empty-state />@endif</div>
</x-admin.layout>
