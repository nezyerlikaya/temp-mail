<x-admin.layout :title="'Preview: '.$section->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Signed preview"
        :title="$section->title"
        description="Per-language Sections Studio preview. Public theme chrome remains theme-owned."
    >
        <x-slot:actions>
            <x-sections.status-badge :status="$section->status" />
            <a href="{{ route('admin.sections-studio.edit', $section) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to editor</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="border-b border-stone-200 pb-5">
                <div class="flex flex-wrap gap-2">
                    <x-sections.language-badge :locale="$section->locale" />
                    <x-sections.type-badge :type="$section->section_type" />
                </div>
                <h1 class="mt-4 text-3xl font-extrabold text-stone-950">{{ $section->title }}</h1>
                @if ($section->subtitle)
                    <p class="mt-3 max-w-3xl text-base leading-7 text-stone-600">{{ $section->subtitle }}</p>
                @endif
            </div>

            @if ($renderReadiness['renderable'])
                <div class="mt-6 space-y-5">
                    @if ($section->content)
                        <div class="whitespace-pre-line text-sm leading-7 text-stone-800">{{ $section->content }}</div>
                    @endif

                    @if (($renderReadiness['payload']['items'] ?? []) !== [])
                        <div class="grid gap-3">
                            @foreach ($renderReadiness['payload']['items'] as $item)
                                <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                                    <p class="font-extrabold text-stone-950">{{ $item['title'] }}</p>
                                    @if ($item['content'])
                                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $item['content'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (($renderReadiness['payload']['button_label'] ?? null) || ($renderReadiness['payload']['button_url'] ?? null))
                        <div class="rounded-lg border border-teal-200 bg-teal-50 p-4 text-sm font-bold text-teal-950">
                            CTA: {{ $renderReadiness['payload']['button_label'] ?? 'Button label pending' }}
                        </div>
                    @endif
                </div>
            @else
                <x-admin.alert variant="warning" title="No public placeholder">
                    {{ $renderReadiness['message'] }}
                </x-admin.alert>
            @endif
        </article>

        <aside class="space-y-6">
            <x-sections.render-readiness :readiness="$renderReadiness" />
            <x-sections.seo-readiness :readiness="$seoReadiness" />
            <x-sections.theme-contract :contracts="$themeContracts" />
        </aside>
    </div>
</x-admin.layout>
