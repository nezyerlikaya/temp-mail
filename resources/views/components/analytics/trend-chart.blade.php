@props(['title', 'points'])

@php($max = max(1, collect($points)->max('value') ?? 0))

<div class="rounded-lg border border-stone-200 bg-white p-4">
    <div class="flex items-start justify-between gap-3">
        <h3 class="text-sm font-extrabold text-stone-950">{{ $title }}</h3>
        <span class="text-xs font-bold text-stone-500">{{ collect($points)->sum('value') }} total</span>
    </div>
    @if(collect($points)->sum('value') > 0)
        <div class="mt-4 flex h-32 items-end gap-1" aria-label="{{ $title }} trend">
            @foreach($points as $point)
                <div class="flex min-w-0 flex-1 flex-col items-center gap-1">
                    <div class="w-full rounded-t bg-teal-700" style="height: {{ max(6, (int) (($point['value'] / $max) * 120)) }}px" title="{{ $point['date'] }}: {{ $point['value'] }}"></div>
                    <span class="sr-only">{{ $point['date'] }} {{ $point['value'] }}</span>
                </div>
            @endforeach
        </div>
    @else
        <x-analytics.empty-state title="No trend data" description="Daily aggregate rows will populate this chart after analytics aggregation runs." class="min-h-32 py-6" />
    @endif
</div>
