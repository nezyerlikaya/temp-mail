@props(['backup', 'integrity', 'canDownload' => false, 'canDelete' => false])

<tr class="align-top transition hover:bg-stone-50/80">
    <td class="px-5 py-4">
        <p class="text-sm font-extrabold text-stone-950">{{ $backup->filename ?? 'Backup '.$backup->uuid }}</p>
        <p class="mt-1 max-w-md truncate text-xs font-semibold text-stone-500">{{ $backup->uuid }}</p>
        @if ($backup->failure_reason)
            <p class="mt-2 text-sm font-semibold text-red-700">{{ $backup->failure_reason }}</p>
        @endif
    </td>
    <td class="px-5 py-4">
        <span class="text-sm font-bold text-stone-800">{{ str($backup->type)->headline() }}</span>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-system.backup-status-badge :status="$backup->status" />
            <x-system.integrity-badge :result="$integrity" />
        </div>
    </td>
    <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">
        <span class="font-bold text-stone-900">{{ number_format($backup->size_bytes / 1024, 2) }} KB</span>
        <span class="mt-1 block text-xs text-stone-500">SHA-256 ready</span>
    </td>
    <td class="px-5 py-4 text-sm text-stone-600">
        <span class="block font-bold text-stone-900">{{ $backup->creator?->name ?? 'System' }}</span>
        <span class="mt-1 block text-xs text-stone-500">{{ $backup->created_at?->format('M j, Y H:i') }}</span>
    </td>
    <td class="px-5 py-4">
        <div class="flex flex-wrap justify-end gap-2">
            @if ($canDownload && $backup->status === 'completed')
                <a href="{{ route('admin.backups-health.download', $backup) }}" class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/15">
                    <i data-lucide="download" class="size-4" aria-hidden="true"></i>
                    Download
                </a>
            @endif
            @if ($canDelete)
                <form method="POST" action="{{ route('admin.backups-health.destroy', $backup) }}" x-on:submit="if (! confirm('Delete this backup? Restore is not available in MVP.')) { $event.preventDefault() }">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-md border border-red-200 bg-white px-3 text-sm font-bold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-700/15">
                        <i data-lucide="trash-2" class="size-4" aria-hidden="true"></i>
                        Delete
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
