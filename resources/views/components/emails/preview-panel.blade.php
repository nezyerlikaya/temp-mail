@props(['preview', 'template'])

<x-admin.card title="Safe preview" description="Rendered with sample data and the locked system email layout.">
    <div class="mb-4 rounded-lg border border-stone-200 bg-stone-50 p-3">
        <p class="text-xs font-extrabold uppercase text-stone-500">Subject preview</p>
        <p class="mt-1 text-sm font-extrabold text-stone-950">{{ $preview['subject'] }}</p>
        @if ($preview['preheader'])
            <p class="mt-1 text-sm text-stone-600">{{ $preview['preheader'] }}</p>
        @endif
    </div>

    <x-emails.preview-device-tabs :preview="$preview" />
</x-admin.card>
