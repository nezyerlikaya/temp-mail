<x-admin.layout :title="$section->title" :user="$adminUser">
    <x-admin.page-header eyebrow="Sections Studio" :title="$section->title" description="Edit section content, type settings, visibility, and repeatable items.">
        <x-slot:actions>
            <x-sections.status-badge :status="$section->status" />
            @if ($editor['canPreview'])
                <x-sections.preview-button :url="$preview['preview_url']" />
            @endif
            <a href="{{ route('admin.sections-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to sections</a>
        </x-slot:actions>
    </x-admin.page-header>
    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    <x-sections.validation-summary />
    <x-sections.section-editor :section="$section" :editor="$editor" :action="route('admin.sections-studio.update', $section)" method="PUT" />

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-sections.render-readiness :readiness="$renderReadiness" />
            <x-sections.lifecycle-actions
                :section="$section"
                :can-activate="$editor['canActivate']"
                :can-hide="$editor['canHide']"
                :can-trash="$editor['canTrash']"
                :can-restore="$editor['canRestore']"
            />
        </div>

        <div class="min-w-0 space-y-6">
            <x-sections.seo-readiness :readiness="$seoReadiness" />
            <x-sections.theme-contract :contracts="$themeContracts" />
            <x-sections.delete-warning :section="$section" :can-delete="$editor['canDelete']" />
        </div>
    </div>
</x-admin.layout>
