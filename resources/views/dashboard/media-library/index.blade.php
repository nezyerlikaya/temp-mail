<x-admin.layout title="Media Library" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Media Library"
        description="A safe foundation for reusable images, documents, avatars, and SEO assets."
    >
        <x-slot:actions>
            <x-media.status-badge status="Active" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <x-media.library-layout :summary="$summary">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <main class="min-w-0 space-y-6">
                <x-media.filter-bar :filters="$filters" :upload-targets="$uploadTargets" />

                @if ($media->count() > 0)
                    <section class="space-y-6" aria-labelledby="media-results-title">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <h2 id="media-results-title" class="text-lg font-extrabold text-stone-950">Media results</h2>
                                <p class="mt-1 text-sm text-stone-600">Filter by type, uploader, date, or asset text.</p>
                            </div>
                            <p class="text-sm font-bold text-stone-500">{{ $media->total() }} records</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($media as $asset)
                                <x-media.asset-card :asset="$asset" :url="$urls[$asset->id]" />
                            @endforeach
                        </div>

                        <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">Asset</th>
                                            <th scope="col" class="px-4 py-3">Type</th>
                                            <th scope="col" class="px-4 py-3">Uploader</th>
                                            <th scope="col" class="px-4 py-3">Status</th>
                                            <th scope="col" class="px-4 py-3">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($media as $asset)
                                            <x-media.asset-row :asset="$asset" :url="$urls[$asset->id]" />
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            {{ $media->links() }}
                        </div>
                    </section>
                @else
                    <x-media.empty-state />
                @endif
            </main>

            <aside class="min-w-0 space-y-6">
                <x-media.upload-panel :can-upload="$canUploadMedia" :targets="$uploadTargets" />

                <x-admin.card title="Recent uploads" description="Quick glance at the latest library entries.">
                    @if ($recent->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recent as $asset)
                                <div class="flex items-center gap-3 rounded-lg border border-stone-200 p-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-md bg-stone-100">
                                        @if (str_starts_with($asset->mime_type, 'image/'))
                                            <img src="{{ $recentUrls[$asset->id] }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-[10px] font-bold uppercase text-stone-500">{{ $asset->type }}</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-extrabold text-stone-950">{{ $asset->title ?: $asset->original_name }}</p>
                                        <p class="truncate text-xs text-stone-500">{{ $asset->original_name }}</p>
                                    </div>
                                    <x-media.status-badge :status="$asset->status" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-media.empty-state />
                    @endif
                </x-admin.card>
            </aside>
        </div>
    </x-media.library-layout>
</x-admin.layout>
