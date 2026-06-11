@props(['health', 'lastCheck' => null])

<section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm" aria-labelledby="health-summary-title">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-bold text-stone-500">System Health</p>
            <h2 id="health-summary-title" class="mt-1 text-xl font-extrabold text-stone-950">Operations Health Center</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">
                Last checked:
                <span class="font-bold text-stone-800">{{ $lastCheck?->checked_at?->format('M j, Y H:i') ?? 'Preview only, not stored yet' }}</span>
            </p>
        </div>
        <x-system.health-status-badge :status="$health['overall_status']" />
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Healthy', 'value' => $health['summary']['healthy'], 'class' => 'text-emerald-700'],
            ['label' => 'Needs attention', 'value' => $health['summary']['attention'], 'class' => 'text-amber-700'],
            ['label' => 'Critical', 'value' => $health['summary']['critical'], 'class' => 'text-red-700'],
        ] as $metric)
            <div class="rounded-md border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-bold text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-2 text-2xl font-extrabold {{ $metric['class'] }}">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>
</section>
