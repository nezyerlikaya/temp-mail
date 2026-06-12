@props(['missing'])

<x-admin.card title="Missing required templates" description="Create each missing language/key pair manually.">
    @if ($missing->count() > 0)
        <div class="space-y-2">
            @foreach ($missing->take(8) as $item)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                    <p class="text-sm font-extrabold text-amber-950">{{ $item['label'] }}</p>
                    <p class="text-xs font-bold text-amber-800">{{ $item['locale']->language_name }} · {{ $item['locale']->locale }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-800">No missing template records.</p>
    @endif
</x-admin.card>
