@props(['categories'])

<div class="space-y-3">
    @foreach ($categories as $category)
        <div>
            <div class="mb-1 flex items-center justify-between gap-3">
                <span class="text-xs font-bold text-stone-600">{{ $category['label'] }}</span>
                <span class="text-xs font-extrabold text-stone-950">{{ $category['score'] }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-stone-100">
                <div class="h-full rounded-full {{ $category['score'] >= 85 ? 'bg-emerald-500' : ($category['score'] >= 70 ? 'bg-teal-500' : 'bg-amber-500') }}" style="width: {{ $category['score'] }}%"></div>
            </div>
        </div>
    @endforeach
</div>
