@props(['suggestions'])

<x-admin.card title="Palette Suggestions" description="Safe suggestions generated from the selected brand color. Apply them manually to controlled color fields.">
    <div class="space-y-3">
        @foreach ($suggestions as $suggestion)
            <div class="flex items-center gap-3 rounded-md border border-stone-200 p-3">
                <span class="size-9 rounded-md border border-stone-200" style="background-color: {{ $suggestion['value'] }}" aria-hidden="true"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold text-stone-950">{{ $suggestion['label'] }} · {{ $suggestion['value'] }}</span>
                    <span class="block text-xs font-semibold text-stone-500">{{ $suggestion['purpose'] }}</span>
                </span>
            </div>
        @endforeach
    </div>
</x-admin.card>
