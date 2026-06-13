@props(['warnings' => []])

<x-admin.card title="Pairing" description="Heading and body readability checks.">
    @if ($warnings)
        <div class="space-y-2">
            @foreach ($warnings as $warning)
                <p class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-950">{{ $warning['message'] }}</p>
            @endforeach
        </div>
    @else
        <p class="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-950">Heading and body pairing looks consistent.</p>
    @endif
</x-admin.card>
