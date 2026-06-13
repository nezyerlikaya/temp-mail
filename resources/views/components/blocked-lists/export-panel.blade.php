@props(['canExport', 'filters', 'canViewSensitiveIp'])
@if ($canExport)
    <section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-extrabold uppercase text-teal-800">CSV export</p>
        <h2 class="mt-1 text-lg font-extrabold text-stone-950">Export reviewed rules</h2>
        <p class="mt-1 text-sm text-stone-600">Exports are audited. Sensitive IP values stay masked unless permitted.</p>
        <form method="GET" action="{{ route('admin.blocked-lists.export') }}" class="mt-4 space-y-3">
            <input type="hidden" name="entry_type" value="{{ $filters['entry_type'] }}">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            <input type="hidden" name="source" value="{{ $filters['source'] }}">
            @if ($canViewSensitiveIp)
                <label class="flex items-center gap-2 text-sm font-semibold text-stone-700"><input type="checkbox" name="include_sensitive_ip" value="1" class="rounded border-stone-300 text-teal-700 focus:ring-teal-700"> Include full IP values</label>
            @endif
            <button class="inline-flex min-h-10 items-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white">Download CSV</button>
        </form>
    </section>
@endif
