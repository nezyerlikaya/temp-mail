@props(['title' => 'No analytics data yet', 'description' => 'Aggregate rows will appear after events are tracked and analytics aggregation runs.'])

<div {{ $attributes->merge(['class' => 'flex min-h-44 flex-col items-center justify-center rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-8 text-center']) }}>
    <i data-lucide="chart-no-axes-combined" class="size-6 text-stone-500" aria-hidden="true"></i>
    <p class="mt-3 text-sm font-extrabold text-stone-950">{{ $title }}</p>
    <p class="mt-1 max-w-sm text-sm leading-6 text-stone-600">{{ $description }}</p>
</div>
