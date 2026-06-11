@props(['current' => 'manifest'])

@php
    $steps = [
        ['key' => 'manifest', 'label' => 'Manifest', 'description' => 'Fetch release metadata'],
        ['key' => 'compatibility', 'label' => 'Compatibility', 'description' => 'Check server readiness'],
        ['key' => 'install', 'label' => 'Install', 'description' => 'Locked, verified package install'],
    ];
@endphp

<ol {{ $attributes->merge(['class' => 'grid gap-3 sm:grid-cols-3']) }} aria-label="Update workflow">
    @foreach ($steps as $step)
        @php
            $active = $step['key'] === $current;
            $deferred = $step['key'] === 'install';
        @endphp
        <li class="rounded-lg border {{ $active ? 'border-teal-300 bg-teal-50' : ($deferred ? 'border-stone-200 bg-stone-50' : 'border-stone-200 bg-white') }} p-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-extrabold text-stone-950">{{ $step['label'] }}</p>
                <x-updates.status-badge :status="$active ? 'ready' : ($deferred ? 'pending' : 'current')" />
            </div>
            <p class="mt-2 text-sm leading-5 text-stone-600">{{ $step['description'] }}</p>
        </li>
    @endforeach
</ol>
