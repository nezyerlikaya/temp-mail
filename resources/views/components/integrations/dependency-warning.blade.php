@props(['warnings' => []])

@if (count($warnings) > 0)
    <div class="mb-4 space-y-2">
        @foreach ($warnings as $warning)
            @php($classes = ($warning['severity'] ?? 'info') === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-950' : 'border-sky-200 bg-sky-50 text-sky-950')
            <div class="rounded-md border p-3 text-sm font-semibold {{ $classes }}">
                <p class="font-extrabold">{{ $warning['owner'] }}</p>
                <p class="mt-1">{{ $warning['message'] }}</p>
            </div>
        @endforeach
    </div>
@endif
