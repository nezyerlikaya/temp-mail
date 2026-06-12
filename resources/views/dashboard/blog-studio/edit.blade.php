<x-admin.layout :title="$post->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Blog Studio"
        :title="$post->title"
        description="Refine content, media, taxonomy readiness, and publishing state for this language-specific post."
    >
        <x-slot:actions>
            <x-blog.status-badge :status="$post->status" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-blog.validation-summary />

    <x-blog.post-editor :post="$post" :editor="$editor" :action="route('admin.blog-studio.update', $post)" method="PUT" />
</x-admin.layout>
