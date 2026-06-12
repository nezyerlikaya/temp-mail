@props(['record', 'label' => null])

<div class="rounded-lg border border-stone-200 bg-white p-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm font-extrabold text-stone-950">{{ $label ?: $record['type'] }}</p>
            <p class="mt-1 font-mono text-xs font-bold text-stone-500">{{ $record['type'] }} · {{ $record['host'] }}</p>
        </div>
        <x-domains.status-badge :status="$record['status'] ?? 'draft'" />
    </div>
    <dl class="mt-4 grid gap-3 text-sm lg:grid-cols-2">
        <div>
            <dt class="text-xs font-extrabold uppercase text-stone-500">Expected</dt>
            <dd class="mt-1 break-words font-mono text-xs font-semibold text-stone-800">{{ $record['expected'] ?? $record['value'] }}</dd>
        </div>
        <div>
            <dt class="text-xs font-extrabold uppercase text-stone-500">Detected</dt>
            <dd class="mt-1 break-words text-sm font-semibold text-stone-700">
                @if (! empty($record['detected']))
                    {{ implode(', ', $record['detected']) }}
                @else
                    No public record detected
                @endif
            </dd>
        </div>
    </dl>
    @if (! empty($record['guidance']))
        <p class="mt-3 rounded-md bg-stone-50 p-3 text-sm leading-6 text-stone-600">{{ $record['guidance'] }}</p>
    @endif
</div>
