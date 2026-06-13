@props(['grid' => [], 'risks' => [], 'scripts' => [], 'rtlSummary' => []])

<x-admin.card title="Script Coverage" description="Coverage is informational unless the selected locale script is critically missing.">
    <div class="mb-4 grid grid-cols-3 gap-3 text-center">
        <div class="rounded-md bg-stone-50 p-3">
            <p class="text-xs font-bold text-stone-500">RTL locales</p>
            <p class="mt-1 text-lg font-extrabold text-stone-950">{{ $rtlSummary['total'] ?? 0 }}</p>
        </div>
        <div class="rounded-md bg-emerald-50 p-3">
            <p class="text-xs font-bold text-emerald-700">Ready</p>
            <p class="mt-1 text-lg font-extrabold text-emerald-950">{{ $rtlSummary['ready'] ?? 0 }}</p>
        </div>
        <div class="rounded-md bg-amber-50 p-3">
            <p class="text-xs font-bold text-amber-700">Review</p>
            <p class="mt-1 text-lg font-extrabold text-amber-950">{{ $rtlSummary['needs_review'] ?? 0 }}</p>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($grid as $row)
            <div class="rounded-md border border-stone-200 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-stone-950">{{ strtoupper($row['usage']) }}</p>
                        <p class="text-xs font-bold text-stone-500">{{ $row['font'] ?? 'Unresolved' }}</p>
                    </div>
                    <x-typography.coverage-badge :ready="! $row['critical']" />
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($scripts as $script => $label)
                        <span class="rounded-full px-2 py-1 text-xs font-extrabold ring-1 {{ in_array($script, $row['supported'], true) ? 'bg-teal-50 text-teal-900 ring-teal-100' : 'bg-stone-100 text-stone-500 ring-stone-200' }}">{{ $label }}</span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if ($risks)
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-950" role="status">
            @foreach ($risks as $risk)
                <p>{{ $risk['message'] }}</p>
            @endforeach
        </div>
    @endif
</x-admin.card>
