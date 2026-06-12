@props(['post', 'preview'])

<x-admin.card title="URL readiness" description="Signed preview is available now; public rendering remains theme-owned readiness.">
    <div class="space-y-4 text-sm">
        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Signed preview</p>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                <code class="min-w-0 flex-1 break-all rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-700">{{ $preview['preview_url'] }}</code>
                <x-blog.preview-button :url="$preview['preview_url']" />
            </div>
            <p class="mt-2 text-xs font-bold text-stone-500">Expires in {{ $preview['expires_in'] }}. Unpublished posts require signed access and admin authorization.</p>
        </div>

        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Public URL readiness</p>
            <code class="mt-2 block break-all rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-700">{{ $preview['public_url'] }}</code>
            <p class="mt-2 text-xs font-bold {{ $preview['public_ready'] ? 'text-teal-700' : 'text-stone-500' }}">
                {{ $preview['public_ready'] ? 'Ready when public blog rendering is connected.' : 'Publish this post before public rendering should resolve it.' }}
            </p>
        </div>

        <div>
            <p class="text-xs font-bold uppercase text-stone-500">Scheduled publish readiness</p>
            <p class="mt-2 rounded-lg border border-stone-200 bg-stone-50 p-3 font-bold text-stone-700">
                {{ $preview['scheduled_ready'] ? 'This post has scheduled publish metadata.' : 'Choose a future published date while publishing to mark this post as scheduled.' }}
            </p>
        </div>
    </div>
</x-admin.card>
