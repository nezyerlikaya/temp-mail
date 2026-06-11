@props(['check'])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-extrabold uppercase text-stone-500">{{ $check['group'] }}</p>
            <h3 class="mt-1 text-base font-extrabold text-stone-950">{{ $check['label'] }}</h3>
        </div>
        <x-system.health-status-badge :status="$check['status']" />
    </div>
    <p class="mt-4 text-sm font-semibold text-stone-800">{{ $check['message'] }}</p>
    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $check['detail'] }}</p>
</article>
