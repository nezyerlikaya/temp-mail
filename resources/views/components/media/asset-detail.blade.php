@props(['asset', 'url', 'canUpdate' => false])

<section class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <header class="border-b border-stone-200 px-5 py-4">
        <h2 class="text-base font-extrabold text-stone-950">Media asset detail</h2>
        <p class="mt-1 text-sm text-stone-600">Metadata only. No unsafe path exposure.</p>
    </header>

    <div class="grid gap-6 p-5 lg:grid-cols-[320px_minmax(0,1fr)]">
        <div class="space-y-4">
            <div class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50 p-3">
                @if (str_starts_with($asset->mime_type, 'image/'))
                    <img src="{{ $url }}" alt="{{ $asset->alt_text ?: $asset->title ?: $asset->original_name }}" class="h-auto w-full rounded-md object-cover">
                @else
                    <div class="flex aspect-[4/3] items-center justify-center rounded-md bg-white text-sm font-bold text-stone-500">{{ strtoupper($asset->type) }}</div>
                @endif
            </div>

            <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div><dt class="text-xs font-bold uppercase text-stone-500">File name</dt><dd class="mt-1 break-all text-sm font-extrabold text-stone-950">{{ $asset->file_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-stone-500">Disk</dt><dd class="mt-1 text-sm font-extrabold text-stone-950">{{ $asset->disk }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-stone-500">Mime type</dt><dd class="mt-1 text-sm font-extrabold text-stone-950">{{ $asset->mime_type }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-stone-500">Size</dt><dd class="mt-1 text-sm font-extrabold text-stone-950">{{ number_format($asset->size_bytes / 1024, 1) }} KB</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-stone-500">Dimensions</dt><dd class="mt-1 text-sm font-extrabold text-stone-950">{{ $asset->width ?: 'Unknown' }} x {{ $asset->height ?: 'Unknown' }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase text-stone-500">Uploaded by</dt><dd class="mt-1 text-sm font-extrabold text-stone-950">{{ $asset->uploader?->name ?? 'System' }}</dd></div>
                </dl>

                <a href="{{ $url }}" target="_blank" rel="noreferrer" class="mt-5 inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    Open public file
                </a>
            </div>
        </div>

        <div>
            <form
                method="POST"
                action="{{ route('admin.media-library.update', $asset) }}"
                class="space-y-4"
                x-data="{ submitting: false }"
                x-on:submit="if (submitting) { $event.preventDefault(); return } submitting = true"
                x-bind:aria-busy="submitting"
                x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
            >
                @csrf
                @method('PUT')

                <div>
                    <label for="media-title-detail" class="text-sm font-extrabold text-stone-950">Title</label>
                    <input
                        id="media-title-detail"
                        name="title"
                        value="{{ old('title', $asset->title) }}"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('title') aria-invalid="true" aria-describedby="media-title-detail-error" @enderror
                        @disabled(! $canUpdate)
                    >
                    @error('title')
                        <p id="media-title-detail-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="media-alt-detail" class="text-sm font-extrabold text-stone-950">Alt text</label>
                    <input
                        id="media-alt-detail"
                        name="alt_text"
                        value="{{ old('alt_text', $asset->alt_text) }}"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('alt_text') aria-invalid="true" aria-describedby="media-alt-detail-error" @enderror
                        @disabled(! $canUpdate)
                    >
                    @error('alt_text')
                        <p id="media-alt-detail-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="media-caption-detail" class="text-sm font-extrabold text-stone-950">Caption</label>
                    <textarea
                        id="media-caption-detail"
                        name="caption"
                        rows="4"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('caption') aria-invalid="true" aria-describedby="media-caption-detail-error" @enderror
                        @disabled(! $canUpdate)
                    >{{ old('caption', $asset->caption) }}</textarea>
                    @error('caption')
                        <p id="media-caption-detail-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canUpdate)>
                    <span x-show="! submitting">Save metadata</span>
                    <span x-cloak x-show="submitting">Saving...</span>
                </button>
            </form>
        </div>
    </div>
</section>
