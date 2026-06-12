<x-admin.layout :title="'Preview: '.$page->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Signed preview"
        :title="$page->title"
        description="Temporary Page Studio preview. Public theme rendering is intentionally separate."
    >
        <x-slot:actions>
            <x-pages.status-badge :status="$page->status" />
            <a href="{{ route('admin.page-studio.edit', $page) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to editor</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap gap-2">
                    <x-pages.language-badge :locale="$page->locale" />
                    <x-pages.legal-page-badge :readiness="$legal" />
                </div>
                <h1 class="mt-4 text-3xl font-extrabold text-stone-950">{{ $page->title }}</h1>
                @if ($page->excerpt)
                    <p class="mt-3 max-w-3xl text-base leading-7 text-stone-600">{{ $page->excerpt }}</p>
                @endif
            </div>

            <div class="prose prose-stone mt-6 max-w-none whitespace-pre-line text-sm leading-7 text-stone-800">{{ $page->content ?: 'Content preview is empty.' }}</div>
        </article>

        <aside class="space-y-6">
            <x-pages.page-url-panel :page="$page" :preview="$preview" />
        </aside>
    </div>
</x-admin.layout>
