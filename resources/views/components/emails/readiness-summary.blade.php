@props(['readiness'])

<x-admin.card title="Readiness coverage" description="Language-specific active template coverage. Readiness is informational and does not replace validation.">
    <div class="grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)] lg:items-center">
        <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
            <p class="text-xs font-extrabold uppercase text-stone-500">Overall readiness</p>
            <p class="mt-2 text-3xl font-extrabold text-stone-950">{{ $readiness['score'] }}%</p>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ $readiness['active'] }}/{{ $readiness['expected'] }} active templates</p>
        </div>
        <div class="grid gap-3 md:grid-cols-3">
            @foreach ($readiness['languages']->take(6) as $language)
                <div class="rounded-lg border border-stone-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-extrabold text-stone-950">{{ $language['locale']->language_name }}</p>
                        <span class="text-xs font-extrabold text-stone-500">{{ $language['score'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-stone-100">
                        <div class="h-full rounded-full bg-teal-600" style="width: {{ $language['score'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-admin.card>
