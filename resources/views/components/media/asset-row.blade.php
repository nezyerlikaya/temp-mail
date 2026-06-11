@props(['asset', 'url'])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3">
        <a href="{{ route('admin.media-library.edit', $asset) }}" class="font-extrabold text-stone-950 hover:text-teal-700">{{ $asset->title ?: $asset->original_name }}</a>
        <p class="mt-1 text-xs text-stone-500">{{ $asset->original_name }}</p>
    </td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $asset->type }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $asset->uploader?->name ?? 'System' }}</td>
    <td class="px-4 py-3"><x-media.status-badge :status="$asset->status" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $asset->created_at?->format('M j, Y') }}</td>
</tr>
