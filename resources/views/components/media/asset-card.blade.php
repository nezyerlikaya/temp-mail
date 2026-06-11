@props(['asset', 'url'])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <a href="{{ route('admin.media-library.edit', $asset) }}" class="block focus:outline-none">
        <div class="aspect-[4/3] overflow-hidden rounded-lg border border-stone-200 bg-stone-50">
            @if (str_starts_with($asset->mime_type, 'image/'))
                <img src="{{ $url }}" alt="{{ $asset->alt_text ?: $asset->title ?: $asset->original_name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center text-sm font-bold text-stone-500">{{ strtoupper($asset->type) }}</div>
            @endif
        </div>
        <div class="mt-4 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-stone-950">{{ $asset->title ?: $asset->original_name }}</p>
                <p class="mt-1 truncate text-xs text-stone-500">{{ $asset->original_name }}</p>
            </div>
            <x-media.status-badge :status="$asset->status" />
        </div>
        <p class="mt-3 text-xs text-stone-500">{{ $asset->mime_type }} &middot; {{ number_format($asset->size_bytes / 1024, 1) }} KB</p>
    </a>
</article>
