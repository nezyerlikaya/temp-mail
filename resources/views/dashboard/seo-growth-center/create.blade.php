<x-admin.layout title="Create SEO record" :user="$adminUser">
    <x-admin.page-header
        eyebrow="SEO Growth Center"
        title="Create SEO record"
        description="Choose a language and target before opening the full metadata editor."
    >
        <x-slot:actions>
            <a href="{{ route('admin.seo-growth-center.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to SEO</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-seo.validation-summary />

    <x-admin.card title="Target selection" description="SEO records are language-specific and target-specific. Content stays in its source module.">
        <form method="POST" action="{{ route('admin.seo-growth-center.records.ensure') }}" class="space-y-5">
            @csrf
            <x-seo.language-selector :locales="$editor['locales']" />
            <x-seo.target-selector :targets="$editor['targets']" />
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-5 py-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Open editor</button>
        </form>
    </x-admin.card>
</x-admin.layout>
