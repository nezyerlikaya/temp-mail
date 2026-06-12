@props(['section', 'types' => [], 'placements' => []])

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
</tr>
