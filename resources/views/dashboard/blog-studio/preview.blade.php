<x-admin.layout :title="'Preview: '.$post->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Signed preview"
        :title="$post->title"
        description="Temporary Blog Studio preview. Public blog rendering is intentionally separate."
    >
        <x-slot:actions>
            <x-blog.status-badge :status="$post->status" />
            <a href="{{ route('admin.blog-studio.edit', $post) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to editor</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap gap-2">
                    <x-blog.language-badge :locale="$post->locale" />
                    <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">
                        {{ $post->category?->name ?? 'No category' }}
                    </span>
                </div>
                <h1 class="mt-4 text-3xl font-extrabold text-stone-950">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="mt-3 max-w-3xl text-base leading-7 text-stone-600">{{ $post->excerpt }}</p>
                @endif
            </div>

            <div class="prose prose-stone mt-6 max-w-none whitespace-pre-line text-sm leading-7 text-stone-800">{{ $post->content ?: 'Content preview is empty.' }}</div>

            @if ($post->tags->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-2 border-t border-stone-200 pt-5">
                    @foreach ($post->tags as $tag)
                        <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="space-y-6">
            <x-blog.public-url-panel :post="$post" :preview="$preview" />
        </aside>
    </div>
</x-admin.layout>
