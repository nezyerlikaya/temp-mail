@props(['card'])

@php
    $styles = [
        'healthy' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
        'failed' => 'border-red-200 bg-red-50 text-red-950',
    ];
@endphp

<a href="{{ route($card['route']) }}" class="block rounded-lg border p-4 shadow-sm transition hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-teal-600/20 {{ $styles[$card['status']] ?? $styles['warning'] }}">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-extrabold">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-black">{{ $card['metric'] }}</p>
        </div>
        <span class="rounded-md bg-white/70 px-2 py-1 text-xs font-extrabold">{{ str($card['status'])->headline() }}</span>
    </div>
    <p class="mt-3 text-sm leading-6">{{ $card['message'] }}</p>
</a>
