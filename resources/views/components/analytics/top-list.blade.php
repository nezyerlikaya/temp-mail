@props(['title', 'items'])

<div class="rounded-lg border border-stone-200 bg-white p-4">
    <h3 class="text-sm font-extrabold text-stone-950">{{ $title }}</h3>
    @if(count($items))
        <div class="mt-3 space-y-3">
            @foreach($items as $item)
                <div class="flex items-center justify-between gap-3">
                    <span class="min-w-0 truncate text-sm font-bold text-stone-700">{{ $item['label'] }}</span>
                    <span class="text-sm font-extrabold text-stone-950">{{ number_format((int) $item['value']) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <x-analytics.empty-state title="No aggregate rows" description="This list will populate after daily analytics aggregation." class="min-h-32 py-6" />
    @endif
</div>
