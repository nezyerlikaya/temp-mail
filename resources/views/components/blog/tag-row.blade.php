@props(['tag', 'canUpdate' => false])

<tr class="border-t border-stone-200">
    <td class="px-4 py-4">
        <div class="min-w-0">
            <p class="font-extrabold text-stone-950">{{ $tag->name }}</p>
            <p class="mt-1 truncate text-xs font-bold text-stone-500">/{{ $tag->slug }}</p>
            @if ($tag->description)
                <p class="mt-2 max-w-xl text-sm text-stone-600">{{ $tag->description }}</p>
            @endif
        </div>
    </td>
    <td class="px-4 py-4 text-sm font-bold text-stone-700">{{ $tag->locale?->language_name ?? 'Unknown' }}</td>
    <td class="px-4 py-4"><x-blog.taxonomy-status-badge :status="$tag->status" /></td>
    <td class="px-4 py-4 text-sm font-extrabold text-stone-950">{{ $tag->posts_count }}</td>
    <td class="px-4 py-4 text-right">
        @if ($canUpdate)
            <a href="{{ route('admin.taxonomy.index', ['tab' => 'tags', 'edit_tag' => $tag->id]) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                Edit
            </a>
        @endif
    </td>
</tr>
