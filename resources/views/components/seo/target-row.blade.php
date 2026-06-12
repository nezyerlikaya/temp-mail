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
        <details class="w-full max-w-52">
            <summary class="cursor-pointer rounded-lg border border-stone-300 px-3 py-2 text-center text-xs font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Foundation edit</summary>
            <form method="POST" action="{{ route('admin.seo-growth-center.records.update', $record) }}" class="mt-3 space-y-3 rounded-lg border border-stone-200 bg-stone-50 p-3">
                @csrf
                @method('PUT')

                <label class="block text-xs font-extrabold uppercase text-stone-500" for="seo-meta-title-{{ $record->id }}">Meta title</label>
                <input id="seo-meta-title-{{ $record->id }}" name="meta_title" value="{{ old('meta_title', $record->meta_title) }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">

                <label class="block text-xs font-extrabold uppercase text-stone-500" for="seo-meta-description-{{ $record->id }}">Meta description</label>
                <textarea id="seo-meta-description-{{ $record->id }}" name="meta_description" rows="2" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('meta_description', $record->meta_description) }}</textarea>

                <input type="hidden" name="canonical_url" value="{{ $record->canonical_url }}">
                <input type="hidden" name="robots_follow" value="{{ $record->robots_follow ? '1' : '0' }}">
                <input type="hidden" name="include_in_sitemap" value="{{ $record->include_in_sitemap ? '1' : '0' }}">
                <input type="hidden" name="sitemap_priority" value="{{ $record->sitemap_priority }}">
                <input type="hidden" name="sitemap_change_frequency" value="{{ $record->sitemap_change_frequency }}">
                <input type="hidden" name="twitter_card" value="{{ $record->twitter_card }}">
                <input type="hidden" name="schema_type" value="{{ $record->schema_type }}">

                <label class="flex items-center gap-2 text-sm font-bold text-stone-700">
                    <input type="hidden" name="robots_index" value="0">
                    <input type="checkbox" name="robots_index" value="1" @checked($record->robots_index) class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
                    Index this target
                </label>

                <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canUpdate)>Save foundation</button>
            </form>
        </details>
    </td>
</tr>
