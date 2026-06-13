@props(['preview' => null])
@if ($preview)
    <div class="space-y-3">
        <x-blocked-lists.import-error-list :errors="$preview['errors'] ?? []" />
        <div class="rounded-lg border border-stone-200">
            <div class="grid grid-cols-4 gap-2 border-b border-stone-200 bg-stone-50 px-3 py-2 text-xs font-extrabold text-stone-600">
                <span>Line</span><span>Type</span><span>Value</span><span>Status</span>
            </div>
            @forelse (($preview['rows'] ?? []) as $row)
                <div class="grid grid-cols-4 gap-2 px-3 py-2 text-xs text-stone-700">
                    <span>{{ $row['line'] }}</span><span>{{ $row['entry_type'] }}</span><span class="break-words">{{ $row['display_value'] }}</span><span>{{ $row['valid'] ? 'Ready' : 'Duplicate' }}</span>
                </div>
            @empty
                <p class="px-3 py-3 text-sm text-stone-600">No preview rows available.</p>
            @endforelse
        </div>
    </div>
@endif
