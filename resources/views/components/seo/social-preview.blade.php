@props(['preview'])

<x-admin.card title="Social preview" description="Open Graph and Twitter/X cards use SEO metadata and selected Media Library images.">
    <div class="space-y-4">
        <div class="overflow-hidden rounded-lg border border-stone-200 bg-white">
            <div class="flex aspect-[1.91/1] items-center justify-center bg-stone-100 text-sm font-bold text-stone-500">
                <span x-text="ogImageLabel || @js(($preview['social']['og_image']['title'] ?? null) ?: 'OG image pending')"></span>
            </div>
            <div class="p-4">
                <p class="truncate text-sm font-extrabold text-stone-950" x-text="ogTitle || metaTitle || @js($preview['social']['og_title'] ?? 'Open Graph title pending')"></p>
                <p class="mt-1 line-clamp-2 text-sm text-stone-600" x-text="ogDescription || metaDescription || @js($preview['social']['og_description'] ?? 'Open Graph description pending')"></p>
            </div>
        </div>
        <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
            <p class="text-xs font-bold uppercase text-stone-500" x-text="twitterCard"></p>
            <p class="mt-2 truncate text-sm font-extrabold text-stone-950" x-text="twitterTitle || ogTitle || metaTitle || @js($preview['social']['twitter_title'] ?? 'Twitter title pending')"></p>
            <p class="mt-1 line-clamp-2 text-sm text-stone-600" x-text="twitterDescription || ogDescription || metaDescription || @js($preview['social']['twitter_description'] ?? 'Twitter description pending')"></p>
        </div>
    </div>
</x-admin.card>
