@props(['summary', 'environment'])

<div class="mb-4 rounded-lg border border-stone-200 bg-white p-3">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-extrabold text-stone-950">Provider readiness</p>
        <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-stone-200">{{ $environment }}</span>
    </div>
    <div class="mt-3 grid grid-cols-2 gap-2 text-xs font-bold text-stone-600 sm:grid-cols-5">
        @foreach (['connected' => 'Connected', 'degraded' => 'Degraded', 'failed' => 'Failed', 'disabled' => 'Disabled', 'not_tested' => 'Not tested'] as $key => $label)
            <div class="rounded-md bg-stone-50 p-2 ring-1 ring-stone-200">
                <p class="text-stone-500">{{ $label }}</p>
                <p class="mt-1 text-base font-extrabold text-stone-950">{{ $summary[$key] ?? 0 }}</p>
            </div>
        @endforeach
    </div>
</div>
