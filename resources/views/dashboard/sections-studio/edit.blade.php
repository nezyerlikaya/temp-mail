<x-admin.layout :title="$section->title" :user="$adminUser">
    <x-admin.page-header eyebrow="Sections Studio" :title="$section->title" description="Edit section content, type settings, visibility, and repeatable items.">
        <x-slot:actions>
            <x-sections.status-badge :status="$section->status" />
            <a href="{{ route('admin.sections-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to sections</a>
        </x-slot:actions>
    </x-admin.page-header>
    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    <x-sections.validation-summary />
    <x-sections.section-editor :section="$section" :editor="$editor" :action="route('admin.sections-studio.update', $section)" method="PUT" />
</x-admin.layout>
