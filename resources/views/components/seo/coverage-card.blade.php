@props(['coverage'])

<div class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $coverage['locale']->language_name }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ $coverage['locale']->locale }} · {{ $coverage['ready'] }}/{{ $coverage['records'] }} records ready</p>
        </div>
        <x-seo.severity-badge :severity="$coverage['score'] >= 80 ? 'ready' : ($coverage['score'] >= 50 ? 'warning' : 'critical')" />
    </div>
    <div class="mt-4 h-2 overflow-hidden rounded-full bg-stone-100">
        <div class="h-full rounded-full bg-teal-600" style="width: {{ $coverage['score'] }}%"></div>
    </div>
    <p class="mt-2 text-xs font-extrabold text-stone-600">{{ $coverage['score'] }}% metadata coverage</p>
</div>
