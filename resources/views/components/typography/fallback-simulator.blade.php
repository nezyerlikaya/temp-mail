@props(['simulation' => []])

<x-admin.card title="Fallback Simulator" description="Primary font disabled simulation for resolved public stacks.">
    <div class="space-y-3">
        @foreach ($simulation as $row)
            <div class="rounded-md border border-stone-200 p-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-extrabold text-stone-950">{{ strtoupper($row['usage']) }}</p>
                    <span class="rounded-full px-2 py-1 text-xs font-extrabold ring-1 {{ $row['has_fallback'] ? 'bg-emerald-50 text-emerald-900 ring-emerald-100' : 'bg-amber-50 text-amber-900 ring-amber-100' }}">{{ $row['has_fallback'] ? 'Fallback ready' : 'Missing fallback' }}</span>
                </div>
                <p class="mt-1 text-xs font-bold text-stone-500">Primary disabled: {{ $row['primary'] }}</p>
                <code class="mt-2 block break-words text-xs font-bold text-stone-700">{{ $row['simulated_stack'] }}</code>
            </div>
        @endforeach
    </div>
</x-admin.card>
