@props(['metric'])

@php
    $tone = [
        'critical' => 'border-red-200 bg-red-50 text-red-950',
        'attention' => 'border-amber-200 bg-amber-50 text-amber-950',
        'neutral' => 'border-stone-200 bg-white text-stone-950',
    ][$metric['tone'] ?? 'neutral'] ?? 'border-stone-200 bg-white text-stone-950';
@endphp

<article
    class="rounded-lg border p-4 shadow-sm transition duration-500 {{ $tone }}"
    x-bind:class="metricChanged('{{ $metric['key'] }}') ? 'ring-4 ring-teal-600/20 scale-[1.01]' : ''"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold">{{ $metric['label'] }}</p>
            <p class="mt-2 text-2xl font-extrabold" x-text="metricValue('{{ $metric['key'] }}')">{{ $metric['value'] }}</p>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-md bg-white/80 ring-1 ring-black/5">
            <i data-lucide="{{ $metric['icon'] }}" class="size-5" aria-hidden="true"></i>
        </span>
    </div>
    <p class="mt-3 text-xs font-semibold opacity-80">{{ $metric['detail'] }}</p>
</article>
