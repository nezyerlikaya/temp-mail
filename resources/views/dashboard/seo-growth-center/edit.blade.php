<x-admin.layout :title="'SEO: '.$record->target_key" :user="$adminUser">
    <x-admin.page-header
        eyebrow="SEO Growth Center"
        :title="$record->target_key"
        description="Edit search metadata, robots, sitemap, social cards, schema, and media readiness."
    >
        <x-slot:actions>
            <x-seo.status-badge :status="$record->robots_index ? 'ready' : 'noindex'" />
            <a href="{{ route('admin.seo-growth-center.index', ['locale' => $record->locale?->locale ?? 'all', 'target_type' => $record->target_type]) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to SEO</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-seo.validation-summary />
    <x-seo.editor :record="$record" :editor="$editor" :action="route('admin.seo-growth-center.records.update', $record)" method="PUT" />
</x-admin.layout>
