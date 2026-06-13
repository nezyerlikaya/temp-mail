@props(['item'])

<div class="py-4 first:pt-0 last:pb-0">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm font-extrabold text-stone-950">{{ $item['title'] }}</p>
        <span class="rounded-full bg-stone-100 px-2 py-1 text-xs font-extrabold text-stone-700">{{ str($item['severity'])->headline() }}</span>
    </div>
    <p class="mt-1 text-sm font-semibold text-stone-600">{{ $item['actor'] }} in {{ str($item['module'])->replace('-', ' ')->headline() }}</p>
    <p class="mt-1 text-xs font-bold text-stone-500">{{ $item['time']?->diffForHumans() }}</p>
</div>
