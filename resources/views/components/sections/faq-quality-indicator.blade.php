@props(['quality'])

@php
    $style = ($quality['state'] ?? 'warning') === 'ideal'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
        : (($quality['state'] ?? '') === 'ready'
            ? 'border-teal-200 bg-teal-50 text-teal-900'
            : 'border-amber-200 bg-amber-50 text-amber-900');
@endphp

<div class="rounded-lg border p-4 {{ $style }}" role="status">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="font-extrabold">FAQ quality</p>
        <span class="text-xs font-extrabold">{{ $quality['active_count'] ?? 0 }} active</span>
    </div>
    <p class="mt-2 text-sm font-bold">{{ $quality['message'] ?? 'Add FAQ items to calculate readiness.' }}</p>
    <p class="mt-1 text-xs">Minimum 4, ideal 6-8, maximum 12.</p>
    @if (! empty($quality['duplicate_questions']))
        <p class="mt-2 text-xs font-extrabold">Duplicates: {{ implode(', ', $quality['duplicate_questions']) }}</p>
    @endif
</div>
