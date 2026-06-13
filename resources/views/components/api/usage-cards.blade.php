@props(['summary'])

<div class="grid gap-3 md:grid-cols-4">
    @foreach([
        ['Requests today', $summary['requests_today']],
        ['This month', $summary['requests_this_month']],
        ['Monthly limit', $summary['limit']],
        ['Remaining', $summary['remaining']],
    ] as [$label, $value])
        <div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-extrabold uppercase text-stone-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ number_format((int) $value) }}</p>
        </div>
    @endforeach
</div>
