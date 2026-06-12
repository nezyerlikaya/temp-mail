<x-admin.layout title="Create section" :user="$adminUser">
    <x-admin.page-header eyebrow="Sections Studio" title="Create section" description="Create an independent language-specific content section.">
        <x-slot:actions>
            <a href="{{ route('admin.sections-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to sections</a>
        </x-slot:actions>
    </x-admin.page-header>
    <x-sections.validation-summary />
    <x-sections.section-editor :section="$section" :editor="$editor" :action="route('admin.sections-studio.store')" />
</x-admin.layout>
