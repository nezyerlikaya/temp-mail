@props(['post'])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3">
        <a href="{{ route('admin.blog-studio.edit', $post) }}" class="font-extrabold text-stone-950 hover:text-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $post->title }}</a>
        <p class="mt-1 font-mono text-xs text-stone-500">/{{ $post->slug }}</p>
    </td>
    <td class="px-4 py-3"><x-blog.language-badge :locale="$post->locale" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $post->category?->name ?? 'No category' }}</td>
    <td class="px-4 py-3"><x-blog.status-badge :status="$post->status" /></td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ str($post->content_readiness)->headline() }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $post->author?->name ?? 'System' }}</td>
    <td class="px-4 py-3 text-sm text-stone-600">{{ $post->created_at?->format('M j, Y') }}</td>
</tr>
