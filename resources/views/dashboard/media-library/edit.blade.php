<x-admin.layout :title="$asset->title ?: 'Media Asset'" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        :title="$asset->title ?: $asset->original_name"
        description="Edit library metadata and keep the public file URL safe through Laravel storage."
    >
        <x-slot:actions>
            <x-media.status-badge :status="$asset->status" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <main class="min-w-0">
            <x-media.asset-detail :asset="$asset" :url="$url" :can-update="$canUpdateMedia" />
        </main>

        <aside class="min-w-0 space-y-6">
            <x-media.usage-panel :usages="$usages" :summary="$usageSummary" />
            <x-admin.card title="Asset overview" description="The library keeps content metadata separate from file storage.">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Storage</dt>
                        <dd class="mt-1 font-extrabold text-stone-950">{{ $asset->disk }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Public URL</dt>
                        <dd class="mt-1 break-all font-mono text-xs text-stone-600">{{ $url }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Uploaded by</dt>
                        <dd class="mt-1 font-extrabold text-stone-950">{{ $asset->uploader?->name ?? 'System' }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
