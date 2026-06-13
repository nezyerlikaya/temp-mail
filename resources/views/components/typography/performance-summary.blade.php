@props(['summary'])

<x-admin.card title="Performance" description="Approximate font load weight and readiness. No browser lab or external API is used.">
    <div class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-md bg-stone-50 p-3">
            <p class="text-xs font-bold text-stone-500">Families</p>
            <p class="mt-1 text-lg font-extrabold text-stone-950">{{ $summary['family_count'] }}</p>
        </div>
        <div class="rounded-md bg-stone-50 p-3">
            <p class="text-xs font-bold text-stone-500">Weights</p>
            <p class="mt-1 text-lg font-extrabold text-stone-950">{{ $summary['weight_count'] }}</p>
        </div>
        <div class="rounded-md bg-stone-50 p-3">
            <p class="text-xs font-bold text-stone-500">Approx.</p>
            <p class="mt-1 text-lg font-extrabold text-stone-950">{{ $summary['estimated_kb'] }} KB</p>
        </div>
    </div>

    <p class="mt-4 rounded-md border border-stone-200 bg-white p-3 text-sm font-extrabold text-stone-800">{{ $summary['readiness'] }}</p>

    @if ($summary['warnings'])
        <div class="mt-3 space-y-2">
            @foreach ($summary['warnings'] as $warning)
                <p class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-950">{{ $warning['message'] }}</p>
            @endforeach
        </div>
    @endif
</x-admin.card>
