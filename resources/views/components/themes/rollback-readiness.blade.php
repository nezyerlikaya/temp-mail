@props(['readiness'])

<x-admin.card title="Rollback Readiness" description="Rollback is handled by activating the previous known theme, without uploads or deletion.">
    <div class="rounded-md border {{ $readiness['ready'] ? 'border-teal-200 bg-teal-50 text-teal-950' : 'border-stone-200 bg-stone-50 text-stone-700' }} p-4">
        <div class="flex items-start gap-3">
            <i data-lucide="{{ $readiness['ready'] ? 'rotate-ccw' : 'circle-dashed' }}" class="mt-0.5 size-5" aria-hidden="true"></i>
            <div>
                <p class="text-sm font-extrabold">{{ $readiness['ready'] ? 'Previous theme ready' : 'Awaiting first change' }}</p>
                <p class="mt-1 text-sm leading-6">{{ $readiness['message'] }}</p>
            </div>
        </div>
    </div>
</x-admin.card>
