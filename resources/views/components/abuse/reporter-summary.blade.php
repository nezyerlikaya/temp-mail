@props(['report', 'canViewSensitive'])
<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <p class="text-xs font-extrabold uppercase text-stone-500">Reporter</p>
    <p class="mt-2 text-sm font-extrabold text-stone-950">{{ $report->reporter_name }}</p>
    @if ($canViewSensitive)
        <p class="mt-1 break-all text-sm font-semibold text-stone-700">{{ $report->reporter_email }}</p>
        <p class="mt-1 truncate font-mono text-xs text-stone-500">IP hash: {{ $report->submitted_ip_hash ?: 'Unavailable' }}</p>
    @else
        <p class="mt-1 text-sm font-semibold text-stone-500">Contact information protected</p>
    @endif
</div>
