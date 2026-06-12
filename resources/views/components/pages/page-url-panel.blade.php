@props(['page', 'preview'])

<x-admin.card title="URL readiness" description="Signed preview is available now; public rendering remains theme-owned readiness.">
    <div class="space-y-4 text-sm">
        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Signed preview</p>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <code class="min-w-0 flex-1 break-all rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-700">{{ $preview['preview_url'] }}</code>
                <x-pages.preview-button :url="$preview['preview_url']" />
            </div>
            <p class="mt-2 text-xs font-bold text-stone-500">Expires in {{ $preview['expires_in'] }}. Unpublished content is not exposed without this signed URL and admin access.</p>
        </div>

        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Public URL readiness</p>
            <code class="mt-2 block break-all rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-700">{{ $preview['public_url'] }}</code>
            <p class="mt-2 text-xs font-bold {{ $preview['public_ready'] ? 'text-teal-700' : 'text-stone-500' }}">
                {{ $preview['public_ready'] ? 'Ready when public theme rendering is connected.' : 'Publish this page before public rendering should resolve it.' }}
            </p>
        </div>
    </div>
</x-admin.card>
