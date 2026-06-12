@props(['locales', 'missingQueue'])

<x-admin.card title="Language readiness" description="Templates are independent per language. Missing records can be created manually.">
    <div class="space-y-3">
        @foreach ($locales->take(6) as $locale)
            <div class="flex items-center justify-between rounded-lg border border-stone-200 px-3 py-2">
                <div>
                    <p class="text-sm font-extrabold text-stone-950">{{ $locale->language_name }}</p>
                    <p class="text-xs font-bold text-stone-500">{{ $locale->locale }}</p>
                </div>
                <span class="text-xs font-extrabold text-stone-500">{{ $locale->is_active ? 'Active market' : 'Passive market' }}</span>
            </div>
        @endforeach
    </div>
    @if ($missingQueue->count() > 0)
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
            <p class="text-sm font-extrabold text-amber-900">Missing templates</p>
            <div class="mt-2 space-y-1">
                @foreach ($missingQueue->take(5) as $missing)
                    <p class="text-xs font-bold text-amber-800">{{ $missing['locale']->locale }} · {{ $missing['label'] }}</p>
                @endforeach
            </div>
        </div>
    @endif
</x-admin.card>
