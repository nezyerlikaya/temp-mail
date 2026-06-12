@props(['deliverability', 'readiness'])

<x-admin.card title="Deliverability readiness" description="Checks are local and do not expose mail secrets.">
    <div class="space-y-3">
        <div class="rounded-lg border {{ $deliverability['ready'] ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }} p-3">
            <p class="text-sm font-extrabold">{{ $deliverability['ready'] ? 'Mail configured' : 'Mail setup needed' }}</p>
            <p class="mt-1 text-sm font-bold">{{ $deliverability['message'] }}</p>
        </div>
        @foreach ($readiness['warnings'] as $warning)
            <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-bold text-amber-900">{{ $warning['message'] }}</p>
        @endforeach
    </div>
</x-admin.card>
