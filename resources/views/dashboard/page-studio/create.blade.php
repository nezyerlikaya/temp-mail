<x-admin.layout title="Create Page" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Page Studio"
        title="Create page"
        description="Draft a language-specific page with content, media, and publishing readiness in one focused workspace."
    >
        <x-slot:actions>
            <a href="{{ route('admin.page-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to pages</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-pages.validation-summary />

    <x-pages.page-editor :page="$page" :editor="$editor" :action="route('admin.page-studio.store')" method="POST" />
</x-admin.layout>
