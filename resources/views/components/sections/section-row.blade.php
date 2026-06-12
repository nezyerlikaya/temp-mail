@props(['section', 'types' => [], 'placements' => [], 'previewUrl' => null, 'canPreview' => false])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3">
        <p class="font-extrabold text-stone-950">{{ $section->title }}</p>
        <p class="mt-1 text-xs text-stone-500">{{ $section->subtitle ?: 'Description readiness pending.' }}</p>
    </td>
    <td class="px-4 py-3"><x-sections.language-badge :locale="$section->locale" /></td>
    <td class="px-4 py-3"><x-sections.type-badge :type="$section->section_type" :types="$types" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $placements[$section->placement] ?? $section->placement }}</td>
    <td class="px-4 py-3"><x-sections.status-badge :status="$section->status" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ str($section->visibility)->headline() }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $section->items_count }}</td>
    <td class="px-4 py-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.sections-studio.edit', $section) }}" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-stone-300 px-3 text-xs font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Edit</a>
            <x-sections.preview-button :url="$previewUrl" :enabled="$canPreview" />
        </div>
    </td>
</tr>
