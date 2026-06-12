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

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-6">
            <x-pages.page-url-panel :page="$page" :preview="$preview" />
            <x-pages.lifecycle-actions
                :page="$page"
                :can-publish="$editor['canPublish']"
                :can-hide="$editor['canHide']"
                :can-trash="$editor['canTrash']"
                :can-restore="$editor['canRestore']"
            />
        </div>

        <div class="min-w-0 space-y-6">
            <x-admin.card title="Legal mapping readiness" description="Settings owns final legal page selection; this page only reports readiness.">
                @if ($legal['is_legal'])
                    <div class="space-y-3 text-sm">
                        <x-pages.legal-page-badge :readiness="$legal" />
                        <p class="text-stone-600">{{ $legal['mapped'] ? 'This page is currently mapped in Settings.' : 'Ready to map from Settings when this legal slot is selected.' }}</p>
                        <a href="{{ route('admin.settings.index', ['group' => 'legal']) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-3 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Open Settings legal hooks</a>
                    </div>
                @else
                    <p class="text-sm text-stone-600">This page type is not one of the legal mapping slots.</p>
                @endif
            </x-admin.card>

            <x-pages.delete-warning :page="$page" :can-delete="$editor['canDelete']" />
        </div>
    </div>
</x-admin.layout>
