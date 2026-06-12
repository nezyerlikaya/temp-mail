@props(['post', 'previewUrl' => null, 'canPreview' => false])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <a href="{{ route('admin.blog-studio.edit', $post) }}" class="block focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-stone-950">{{ $post->title }}</p>
                <p class="mt-1 truncate font-mono text-xs text-stone-500">/{{ $post->slug }}</p>
            </div>
            <x-blog.status-badge :status="$post->status" />
        </div>

        <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-stone-600">{{ $post->excerpt ?: 'Excerpt readiness pending.' }}</p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-blog.language-badge :locale="$post->locale" />
            <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">
                {{ $post->category?->name ?? 'No category' }}
            </span>
            <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">
                {{ str($post->content_readiness)->headline() }}
            </span>
        </div>

        <div class="mt-4 flex items-center justify-between text-xs text-stone-500">
            <span>{{ $post->author?->name ?? 'System' }}</span>
            <span>{{ $post->created_at?->format('M j, Y') }}</span>
        </div>
    </a>

    <div class="mt-4 grid gap-2 sm:grid-cols-2">
        <a href="{{ route('admin.blog-studio.edit', $post) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-3 py-2 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            Edit
        </a>
        <x-blog.preview-button :url="$previewUrl" :enabled="$canPreview && $previewUrl !== null" label="Preview" class="w-full" />
    </div>
</article>
