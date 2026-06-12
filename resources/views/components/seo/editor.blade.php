@props(['record', 'editor', 'action', 'method' => 'POST'])

@php
    $preview = $editor['preview'];
@endphp

<div
    x-data="{
        dirty: false,
        submitting: false,
        metaTitle: @js(old('meta_title', $record->meta_title ?? '')),
        metaDescription: @js(old('meta_description', $record->meta_description ?? '')),
        canonicalUrl: @js(old('canonical_url', $record->canonical_url ?? '')),
        ogTitle: @js(old('og_title', $record->og_title ?? '')),
        ogDescription: @js(old('og_description', $record->og_description ?? '')),
        twitterCard: @js(old('twitter_card', $record->twitter_card ?? 'summary_large_image')),
        twitterTitle: @js(old('twitter_title', $record->twitter_title ?? '')),
        twitterDescription: @js(old('twitter_description', $record->twitter_description ?? '')),
        robotsIndex: @js((bool) old('robots_index', $record->robots_index)),
        ogImageLabel: @js($editor['selectedOgImage']['title'] ?? null)
    }"
    x-on:beforeunload.window="if (dirty && ! submitting) { $event.preventDefault(); $event.returnValue = ''; }"
    class="space-y-6"
>
    <div x-cloak x-show="dirty && ! submitting" class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm font-bold text-amber-900" role="status">
        You have unsaved SEO changes.
    </div>

    <form method="POST" action="{{ $action }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" x-on:input="dirty = true" x-on:change="dirty = true" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true; dirty = false" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <main class="min-w-0 space-y-6">
            <x-admin.card title="Search metadata" description="Title, description, canonical URL, and breadcrumb copy for this target.">
                <div class="space-y-5">
                    <div>
                        <label for="seo-meta-title" class="text-sm font-extrabold text-stone-800">Meta title</label>
                        <input id="seo-meta-title" name="meta_title" x-model="metaTitle" value="{{ old('meta_title', $record->meta_title) }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('meta_title') aria-invalid="true" aria-describedby="seo-meta-title-error" @enderror>
                        @error('meta_title') <p id="seo-meta-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs font-bold" x-bind:class="metaTitle.length >= 45 && metaTitle.length <= 60 ? 'text-emerald-700' : 'text-stone-500'">Title ideal: 45-60 characters. Current: <span x-text="metaTitle.length"></span></p>
                    </div>

                    <div>
                        <label for="seo-meta-description" class="text-sm font-extrabold text-stone-800">Meta description</label>
                        <textarea id="seo-meta-description" name="meta_description" rows="3" x-model="metaDescription" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('meta_description') aria-invalid="true" aria-describedby="seo-meta-description-error" @enderror>{{ old('meta_description', $record->meta_description) }}</textarea>
                        @error('meta_description') <p id="seo-meta-description-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs font-bold" x-bind:class="metaDescription.length >= 120 && metaDescription.length <= 155 ? 'text-emerald-700' : 'text-stone-500'">Description ideal: 120-155 characters. Current: <span x-text="metaDescription.length"></span></p>
                    </div>

                    <div>
                        <label for="seo-canonical-url" class="text-sm font-extrabold text-stone-800">Canonical URL</label>
                        <input id="seo-canonical-url" name="canonical_url" x-model="canonicalUrl" value="{{ old('canonical_url', $record->canonical_url) }}" inputmode="url" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('canonical_url') aria-invalid="true" aria-describedby="seo-canonical-url-error" @enderror>
                        @error('canonical_url') <p id="seo-canonical-url-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="seo-breadcrumb-title" class="text-sm font-extrabold text-stone-800">Breadcrumb title</label>
                        <input id="seo-breadcrumb-title" name="breadcrumb_title" value="{{ old('breadcrumb_title', $record->breadcrumb_title) }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Open Graph and Twitter/X" description="Social cards use SEO metadata and Media Library assets.">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="seo-og-title" class="text-sm font-extrabold text-stone-800">OG title</label>
                        <input id="seo-og-title" name="og_title" x-model="ogTitle" value="{{ old('og_title', $record->og_title) }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    </div>
                    <div>
                        <label for="seo-twitter-title" class="text-sm font-extrabold text-stone-800">Twitter/X title</label>
                        <input id="seo-twitter-title" name="twitter_title" x-model="twitterTitle" value="{{ old('twitter_title', $record->twitter_title) }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    </div>
                    <div>
                        <label for="seo-og-description" class="text-sm font-extrabold text-stone-800">OG description</label>
                        <textarea id="seo-og-description" name="og_description" rows="3" x-model="ogDescription" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('og_description', $record->og_description) }}</textarea>
                    </div>
                    <div>
                        <label for="seo-twitter-description" class="text-sm font-extrabold text-stone-800">Twitter/X description</label>
                        <textarea id="seo-twitter-description" name="twitter_description" rows="3" x-model="twitterDescription" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('twitter_description', $record->twitter_description) }}</textarea>
                    </div>
                    <div>
                        <label for="seo-twitter-card" class="text-sm font-extrabold text-stone-800">Twitter/X card type</label>
                        <select id="seo-twitter-card" name="twitter_card" x-model="twitterCard" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                            @foreach ($editor['twitterCards'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <x-seo.media-picker-field name="og_image_media_id" label="Open Graph image" :selected="$editor['selectedOgImage']" :assets="$editor['mediaAssets']" :ready="$editor['mediaReady']" :can-select="$editor['canSelectMedia']" :can-upload="$editor['canUploadMedia']" />
                    <x-seo.media-picker-field name="twitter_image_media_id" label="Twitter/X image" :selected="$editor['selectedTwitterImage']" :assets="$editor['mediaAssets']" :ready="$editor['mediaReady']" :can-select="$editor['canSelectMedia']" :can-upload="$editor['canUploadMedia']" />
                </div>
            </x-admin.card>

            <x-seo.schema-editor :record="$record" :schema-types="$editor['schemaTypes']" :can-update="$editor['canUpdateSchema']" />
        </main>

        <aside class="min-w-0 space-y-6">
            <x-seo.serp-preview :preview="$preview" />
            <x-seo.social-preview :preview="$preview" />
            <x-seo.robots-control :record="$record" />
            <x-seo.sitemap-control :record="$record" :frequencies="$editor['changeFrequencies']" />

            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                <span x-text="submitting ? 'Saving SEO...' : 'Save SEO record'"></span>
            </button>
        </aside>
    </form>
</div>
