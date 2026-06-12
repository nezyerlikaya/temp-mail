@props(['robots'])

<x-admin.card title="Robots safety" description="Preset readiness only. Robots.txt generation stays guarded.">
    @if ($robots['warnings'] === [])
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-extrabold text-emerald-800">Robots readiness looks safe.</p>
            <p class="mt-1 text-sm text-emerald-700">{{ $robots['total_count'] }} SEO records checked.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($robots['warnings'] as $warning)
                <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-bold text-amber-900">{{ $warning }}</p>
            @endforeach
        </div>
    @endif
</x-admin.card>
