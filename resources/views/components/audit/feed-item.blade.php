@props(['event'])

<tr class="align-top transition hover:bg-stone-50/80">
    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-stone-900">
        {{ $event->created_at?->format('M j, Y') }}
        <span class="block text-xs font-semibold text-stone-500">{{ $event->created_at?->format('H:i:s') }}</span>
    </td>
    <td class="px-5 py-4">
        <div class="flex flex-wrap items-center gap-2">
            <x-audit.module-badge :module="$event->module" />
            <x-audit.severity-badge :severity="$event->severity" />
        </div>
        <p class="mt-2 text-sm font-extrabold text-stone-950">{{ $event->action ?: $event->display_event }}</p>
        <p class="mt-1 text-xs font-semibold text-stone-500">{{ $event->event }}</p>
    </td>
    <td class="px-5 py-4">
        <x-audit.actor-chip :actor="$event->actor" />
        @if ($event->subject)
            <p class="mt-2 text-xs text-stone-500">Target: <span class="font-bold text-stone-700">{{ $event->subject->name }}</span></p>
        @elseif ($event->target_type)
            <p class="mt-2 text-xs text-stone-500">Target ready: <span class="font-bold text-stone-700">{{ class_basename($event->target_type) }} #{{ $event->target_id }}</span></p>
        @endif
    </td>
    <td class="px-5 py-4 text-sm text-stone-600">
        <span class="block">{{ $event->ip_address ?: 'No IP captured' }}</span>
        <span class="mt-1 block max-w-52 truncate text-xs text-stone-500">{{ $event->route_name ?: 'Background/system event' }}</span>
        @if ($event->correlation_id)
            <span class="mt-1 block max-w-52 truncate text-xs text-stone-500">CID {{ $event->correlation_id }}</span>
        @endif
    </td>
    <td class="px-5 py-4">
        @if ($event->metadata)
            <details class="group max-w-xl rounded-md border border-stone-200 bg-white">
                <summary class="cursor-pointer list-none px-3 py-2 text-sm font-bold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-700/15">
                    Metadata
                    <span class="text-xs font-semibold text-stone-500 group-open:hidden">show</span>
                    <span class="hidden text-xs font-semibold text-stone-500 group-open:inline">hide</span>
                </summary>
                <pre class="max-h-48 overflow-auto border-t border-stone-200 bg-stone-50 p-3 text-xs leading-5 text-stone-700">{{ json_encode($event->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @else
            <span class="text-sm text-stone-500">No metadata</span>
        @endif
    </td>
</tr>
