<x-admin.layout title="Create Email Template" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="Create Email Template"
        description="Create one language-specific system email template. Other languages remain independent."
    >
        <x-slot:actions>
            <a href="{{ route('admin.email-templates.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to templates</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-emails.validation-summary />
    <x-emails.template-editor :template="$template" :editor="$editor" :action="route('admin.email-templates.store')" />
</x-admin.layout>
