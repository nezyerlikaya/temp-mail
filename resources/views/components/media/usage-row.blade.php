@props(['usage'])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3">
        <p class="text-sm font-extrabold text-stone-950">{{ $usage->label ?: str($usage->slot)->headline() }}</p>
        <p class="mt-1 text-xs text-stone-500">{{ str($usage->usage_context)->headline() }} / {{ $usage->slot }}</p>
    </td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ str($usage->module)->replace('_', ' ')->headline() }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $usage->usable_type ?: 'Readiness hook' }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $usage->created_at?->format('M j, Y') }}</td>
</tr>
