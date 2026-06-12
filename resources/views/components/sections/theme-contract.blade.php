@props(['contracts' => []])

<x-admin.card title="Theme contract" description="Theme Launch Center stays separate; this module exposes render readiness.">
    <div class="grid gap-3">
        @foreach ($contracts as $contract)
            <div class="rounded-lg border border-stone-200 p-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-extrabold text-stone-950">{{ $contract['theme'] }}</p>
                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-800">{{ str($contract['state'])->headline() }}</span>
                </div>
                <p class="mt-1 text-sm text-stone-600">{{ $contract['message'] }}</p>
                <p class="mt-2 text-xs font-bold text-stone-500">{{ count($contract['supported_types']) }} section types supported</p>
            </div>
        @endforeach
    </div>
</x-admin.card>
