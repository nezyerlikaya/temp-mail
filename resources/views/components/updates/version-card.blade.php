@props(['label', 'version', 'status' => 'pending', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-bold text-stone-500">{{ $label }}</p>
            <p class="mt-2 break-words text-2xl font-extrabold text-stone-950">{{ $version ?: 'Not checked' }}</p>
        </div>
        <x-updates.status-badge :status="$status" />
    </div>
    @if ($description)
        <p class="mt-4 text-sm leading-6 text-stone-600">{{ $description }}</p>
    @endif
</div>
