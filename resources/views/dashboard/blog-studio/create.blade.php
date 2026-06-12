<x-admin.layout title="Create Blog Post" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Blog Studio"
        title="Create post"
        description="Draft a language-specific article with media, taxonomy readiness, and publishing controls."
    >
        <x-slot:actions>
            <a href="{{ route('admin.blog-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to posts</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-blog.validation-summary />

    <x-blog.post-editor :post="$post" :editor="$editor" :action="route('admin.blog-studio.store')" method="POST" />
</x-admin.layout>
