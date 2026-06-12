@props(['record', 'targetTypes' => [], 'canUpdate' => false])

@php
    $metadataStatus = blank($record->meta_title) || blank($record->meta_description) ? 'missing' : 'ready';
    $robotsStatus = $record->robots_index ? 'ready' : 'noindex';
    $sitemapStatus = $record->include_in_sitemap ? 'ready' : 'excluded';
@endphp

<tr class="border-b border-stone-200 last:border-0 align-top">
    <td class="px-4 py-3">
        <p class="font-extrabold text-stone-950">{{ $targetTypes[$record->target_type] ?? str($record->target_type)->headline() }}</p>
            <p class="mt-1 truncate text-xs text-stone-500">{{ $record->target_key }} · {{ $record->locale?->locale }}</p>
        @if ($record->canonical_url)
            <p class="mt-1 max-w-64 truncate text-xs font-bold text-stone-500">{{ $record->canonical_url }}</p>
        @endif
    </td>
    <td class="px-4 py-3">
        <div class="space-y-2">
            <x-seo.status-badge :status="$metadataStatus" />
            <p class="truncate text-sm font-bold text-stone-700">{{ $record->meta_title ?: 'Meta title pending' }}</p>
            <p class="truncate text-xs text-stone-500">{{ $record->meta_description ?: 'Meta description pending' }}</p>
        </div>
    </td>
    <td class="px-4 py-3">
        <div class="space-y-2">
            <x-seo.status-badge :status="$robotsStatus" />
            <p class="text-xs font-bold text-stone-500">{{ $record->robots_follow ? 'Follow' : 'Nofollow' }}</p>
        </div>
    </td>
    <td class="px-4 py-3">
        <div class="space-y-2">
            <x-seo.status-badge :status="$sitemapStatus" />
            <p class="text-xs font-bold text-stone-500">{{ $record->sitemap_change_frequency }} · {{ $record->sitemap_priority }}</p>
        </div>
    </td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $record->schema_type ?: 'Schema readiness pending' }}</td>
    <td class="px-4 py-3">
        <a href="{{ route('admin.seo-growth-center.records.edit', $record) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-stone-300 px-3 text-xs font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Edit</a>
    </td>
</tr>
