@props(['page', 'pageTypes' => []])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3">
        <a href="{{ route('admin.page-studio.edit', $page) }}" class="font-extrabold text-stone-950 hover:text-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $page->title }}</a>
        <p class="mt-1 font-mono text-xs text-stone-500">/{{ $page->slug }}</p>
    </td>
    <td class="px-4 py-3"><x-pages.language-badge :locale="$page->locale" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $pageTypes[$page->page_type] ?? str($page->page_type)->headline() }}</td>
    <td class="px-4 py-3"><x-pages.status-badge :status="$page->status" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $page->author?->name ?? 'System' }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $page->created_at?->format('M j, Y') }}</td>
</tr>
