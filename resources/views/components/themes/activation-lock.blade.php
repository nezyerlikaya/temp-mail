@props(['status'])

<x-admin.card title="Activation Lock" description="Concurrent theme changes are blocked so the public renderer never has two active themes.">
    <div class="rounded-md border {{ $status['locked'] ? 'border-amber-200 bg-amber-50 text-amber-950' : 'border-teal-200 bg-teal-50 text-teal-950' }} p-4">
        <div class="flex items-start gap-3">
            <i data-lucide="{{ $status['locked'] ? 'lock' : 'lock-open' }}" class="mt-0.5 size-5" aria-hidden="true"></i>
            <div>
                <p class="text-sm font-extrabold">{{ $status['locked'] ? 'Activation locked' : 'Ready for activation' }}</p>
                <p class="mt-1 text-sm leading-6">{{ $status['message'] }}</p>
                @if ($status['created_at'])
                    <p class="mt-2 text-xs font-bold">Created {{ $status['created_at'] }}</p>
                @endif
            </div>
        </div>
    </div>
</x-admin.card>
