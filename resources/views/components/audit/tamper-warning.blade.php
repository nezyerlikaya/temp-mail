@props(['readiness'])

@php
    $isReady = ($readiness['status'] ?? 'warning') === 'ready';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 '.($isReady ? 'border-teal-200 bg-teal-50 text-teal-950' : 'border-amber-200 bg-amber-50 text-amber-950')]) }}>
    <div class="flex gap-3">
        <span class="grid size-9 shrink-0 place-items-center rounded-md bg-white/70" aria-hidden="true">
            <i data-lucide="{{ $isReady ? 'shield-check' : 'shield-alert' }}" class="size-5"></i>
        </span>
        <div>
            <p class="text-sm font-extrabold">{{ $readiness['title'] }}</p>
            <p class="mt-1 text-sm leading-6">{{ $readiness['message'] }}</p>
        </div>
    </div>
</div>
