@props(['diskSpace'])

@php
    $freeGb = $diskSpace['free_bytes'] / 1024 / 1024 / 1024;
    $totalGb = $diskSpace['total_bytes'] > 0 ? $diskSpace['total_bytes'] / 1024 / 1024 / 1024 : 0;
    $percentFree = $diskSpace['total_bytes'] > 0 ? min(100, max(0, ($diskSpace['free_bytes'] / $diskSpace['total_bytes']) * 100)) : 0;
@endphp

<x-admin.card title="Disk Space" description="Backups are written outside the public web root.">
    <div class="space-y-4">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-2xl font-extrabold text-stone-950">{{ number_format($freeGb, 2) }} GB</p>
                <p class="mt-1 text-sm text-stone-600">free of {{ number_format($totalGb, 2) }} GB</p>
            </div>
            <x-admin.status-badge :status="$diskSpace['enough'] ? 'Active' : 'Locked'" />
        </div>
        <div class="h-2 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
            <div class="h-full rounded-full bg-teal-700" style="width: {{ $percentFree }}%"></div>
        </div>
        <p class="text-xs leading-5 text-stone-500">Minimum free space required: {{ number_format($diskSpace['minimum_free_bytes'] / 1024 / 1024) }} MB.</p>
    </div>
</x-admin.card>
