@props(['item'])

<div class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm font-extrabold text-stone-950">{{ $item['label'] }}</p>
        <p class="mt-1 text-sm font-semibold text-stone-600">{{ $item['detail'] }}</p>
    </div>
    <a href="{{ route($item['route']) }}" class="inline-flex min-h-9 items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-3 text-xs font-extrabold text-stone-800 transition focus:outline-none focus:ring-4 focus:ring-teal-700/20">
        <span class="size-2 rounded-full {{ ($item['tone'] ?? 'attention') === 'healthy' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
        {{ str($item['status'])->replace('_', ' ')->headline() }}
    </a>
</div>
