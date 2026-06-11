@props(['check'])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-4">
        <p class="font-extrabold text-stone-950">{{ str($check->channel)->headline() }}</p>
        <p class="mt-1 text-xs text-stone-500">{{ $check->checked_at?->diffForHumans() ?? 'Not recorded' }}</p>
    </td>
    <td class="px-4 py-4 text-sm text-stone-700">{{ $check->current_version }} -> {{ $check->latest_version ?: 'Unknown' }}</td>
    <td class="px-4 py-4"><x-updates.status-badge :status="$check->status" /></td>
    <td class="px-4 py-4 text-sm text-stone-600">{{ $check->checker?->name ?? 'System' }}</td>
</tr>
