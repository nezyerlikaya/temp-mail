@props(['searchUrl', 'searchId', 'typeId', 'canUpload' => false])

<template x-teleport="body">
    <div x-cloak x-show="open" class="fixed inset-0 z-[80]" role="presentation">
        <div class="absolute inset-0 bg-stone-950/55 backdrop-blur-sm" x-on:click="open = false" aria-hidden="true"></div>

        <div class="relative flex min-h-full items-start justify-center px-3 py-[7vh] sm:px-6">
            <section
                class="flex max-h-[86vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border border-stone-200 bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                x-bind:aria-labelledby="modalTitleId"
                x-on:keydown.escape.window="open = false"
            >
                <header class="flex items-start justify-between gap-4 border-b border-stone-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-extrabold text-stone-950" x-bind:id="modalTitleId">Choose media</h2>
                        <p class="mt-1 text-sm text-stone-600">Select an existing asset with no manual storage reference.</p>
                    </div>
                    <button type="button" class="grid size-9 place-items-center rounded-md text-stone-500 transition hover:bg-stone-100 hover:text-stone-950 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="open = false" aria-label="Close media picker">
                        <i data-lucide="x" class="size-5" aria-hidden="true"></i>
                    </button>
                </header>

                <div class="border-b border-stone-200 p-4">
                    <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_160px_auto]">
                        <div>
                            <label for="{{ $searchId }}" class="text-xs font-extrabold uppercase text-stone-500">Search</label>
                            <input id="{{ $searchId }}" type="search" x-model.debounce.300ms="query" x-on:input.debounce.300ms="search()" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" placeholder="Title, filename, alt text">
                        </div>
                        <div>
                            <label for="{{ $typeId }}" class="text-xs font-extrabold uppercase text-stone-500">Type</label>
                            <select id="{{ $typeId }}" x-model="type" x-on:change="search()" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                <option value="all">All</option>
                                <option value="image">Images</option>
                                <option value="document">Documents</option>
                                <option value="avatar">Avatars</option>
                                <option value="seo">SEO / OG</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <a href="{{ route('admin.media-library.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                Upload
                            </a>
                        </div>
                    </div>

                    @unless ($canUpload)
                        <p class="mt-3 text-xs font-bold text-stone-500">Upload opens the Media Library page so parent forms are not nested or broken.</p>
                    @endunless
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto p-4">
                    <div class="sr-only" role="status" aria-live="polite" x-text="statusText"></div>

                    <template x-if="loading">
                        <div class="rounded-lg border border-stone-200 bg-stone-50 p-8 text-center text-sm font-bold text-stone-600">Loading media...</div>
                    </template>

                    <template x-if="! loading && assets.length === 0">
                        <div class="rounded-lg border border-stone-200 bg-stone-50 p-8 text-center">
                            <p class="text-sm font-extrabold text-stone-950">No matching assets</p>
                            <p class="mt-1 text-sm text-stone-600">Try a broader search or upload a safe asset first.</p>
                        </div>
                    </template>

                    <div class="grid gap-3 sm:grid-cols-2" x-show="! loading && assets.length > 0">
                        <template x-for="asset in assets" x-bind:key="asset.id">
                            <x-media.picker-result />
                        </template>
                    </div>

                    @if ($canUpload)
                        <div class="mt-5 rounded-lg border border-stone-200 bg-stone-50 p-4">
                            <p class="text-sm font-extrabold text-stone-950">Upload new media</p>
                            <p class="mt-1 text-sm text-stone-600">For a fresh asset, upload directly without leaving the picker.</p>

                            <form method="POST" action="{{ route('admin.media-library.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                                @csrf
                                <input type="hidden" name="type" x-bind:value="type === 'all' ? 'image' : type">
                                <input type="hidden" name="status" value="active">
                                <div>
                                    <label class="text-xs font-extrabold uppercase text-stone-500" for="{{ $searchId }}-upload-file">File</label>
                                    <input id="{{ $searchId }}-upload-file" name="file" type="file" accept="image/*,application/pdf" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                                </div>
                                <div>
                                    <label class="text-xs font-extrabold uppercase text-stone-500" for="{{ $searchId }}-upload-title">Title</label>
                                    <input id="{{ $searchId }}-upload-title" name="title" type="text" class="mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" placeholder="Optional">
                                </div>
                                <div class="flex items-end">
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-stone-950 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</template>
