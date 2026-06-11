<x-admin.layout :title="$page->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Page Studio"
        :title="$page->title"
        description="Refine content, media, and publishing state for this language-specific page."
    >
        <x-slot:actions>
            <x-pages.status-badge :status="$page->status" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-pages.validation-summary />

    <x-pages.page-editor :page="$page" :editor="$editor" :action="route('admin.page-studio.update', $page)" method="PUT" />
</x-admin.layout>
