@props(['preview'])

<x-admin.card title="Google preview" description="Desktop and mobile result previews update while editing.">
    <div class="space-y-4">
        <div class="rounded-lg border border-stone-200 bg-white p-4">
            <p class="truncate text-sm text-stone-600" x-text="canonicalUrl || @js($preview['serp']['desktop']['url'] ?? url('/'))"></p>
            <p class="mt-1 truncate text-xl text-blue-700" x-text="metaTitle || @js($preview['serp']['desktop']['title'] ?? 'Meta title pending')"></p>
            <p class="mt-1 line-clamp-2 text-sm leading-6 text-stone-700" x-text="metaDescription || @js($preview['serp']['desktop']['description'] ?? 'Meta description pending.')"></p>
        </div>
        <div class="max-w-sm rounded-lg border border-stone-200 bg-stone-50 p-4">
            <p class="truncate text-xs text-stone-600" x-text="canonicalUrl || @js($preview['serp']['mobile']['url'] ?? url('/'))"></p>
            <p class="mt-1 line-clamp-2 text-base text-blue-700" x-text="(metaTitle || @js($preview['serp']['mobile']['title'] ?? 'Meta title pending')).slice(0, 62)"></p>
            <p class="mt-1 line-clamp-2 text-xs leading-5 text-stone-700" x-text="(metaDescription || @js($preview['serp']['mobile']['description'] ?? 'Meta description pending.')).slice(0, 128)"></p>
        </div>
    </div>
</x-admin.card>
