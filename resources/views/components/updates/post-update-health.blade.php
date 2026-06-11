@props(['check'])

@php
    $health = $check?->manifest['post_update_health'] ?? null;
@endphp

<div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-stone-950">Post-update health</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">{{ $health['message'] ?? 'Health checks will run after a successful automatic update.' }}</p>
        </div>
        <x-updates.status-badge :status="$health['status'] ?? 'pending'" />
    </div>

    @if (is_array($health['summary'] ?? null))
        <dl class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
            @foreach ($health['summary'] as $label => $value)
                <div class="rounded-lg border border-stone-200 bg-stone-50 p-3">
                    <dt class="font-bold text-stone-500">{{ str($label)->headline() }}</dt>
                    <dd class="mt-1 text-lg font-extrabold text-stone-950">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>
