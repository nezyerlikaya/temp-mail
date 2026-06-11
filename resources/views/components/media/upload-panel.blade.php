@props(['canUpload' => false, 'targets' => []])

<section class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <header class="border-b border-stone-200 px-5 py-4">
        <h2 class="text-base font-extrabold text-stone-950">Upload media</h2>
        <p class="mt-1 text-sm text-stone-600">Store files safely through Laravel storage handling.</p>
    </header>

    <div class="p-5">
        @unless ($canUpload)
            <x-admin.alert variant="warning" title="Upload disabled" class="mb-4">
                Your role can view the library but cannot upload new assets.
            </x-admin.alert>
        @endunless

        <form
            method="POST"
            action="{{ route('admin.media-library.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault(); return } submitting = true"
            x-bind:aria-busy="submitting"
            x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
        >
            @csrf

            <div>
                <label for="media-file" class="text-sm font-extrabold text-stone-950">File</label>
                <input
                    id="media-file"
                    name="file"
                    type="file"
                    accept="image/*,application/pdf"
                    class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-stone-950 file:px-3 file:py-2 file:text-sm file:font-bold file:text-white focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                    @error('file') aria-invalid="true" aria-describedby="media-file-error" @enderror
                    @disabled(! $canUpload)
                >
                @error('file')
                    <p id="media-file-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="media-title" class="text-sm font-extrabold text-stone-950">Title</label>
                    <input
                        id="media-title"
                        name="title"
                        value="{{ old('title') }}"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('title') aria-invalid="true" aria-describedby="media-title-error" @enderror
                        @disabled(! $canUpload)
                    >
                    @error('title')
                        <p id="media-title-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="media-alt" class="text-sm font-extrabold text-stone-950">Alt text</label>
                    <input
                        id="media-alt"
                        name="alt_text"
                        value="{{ old('alt_text') }}"
                        class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                        @error('alt_text') aria-invalid="true" aria-describedby="media-alt-error" @enderror
                        @disabled(! $canUpload)
                    >
                    @error('alt_text')
                        <p id="media-alt-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="media-type" class="text-sm font-extrabold text-stone-950">Type</label>
                    <select id="media-type" name="type" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canUpload)>
                        @foreach ($targets as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'image') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="media-status" class="text-sm font-extrabold text-stone-950">Status</label>
                    <select id="media-status" name="status" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canUpload)>
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="media-caption" class="text-sm font-extrabold text-stone-950">Caption</label>
                <textarea
                    id="media-caption"
                    name="caption"
                    rows="3"
                    class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                    @error('caption') aria-invalid="true" aria-describedby="media-caption-error" @enderror
                    @disabled(! $canUpload)
                >{{ old('caption') }}</textarea>
                @error('caption')
                    <p id="media-caption-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60"
                @disabled(! $canUpload)
            >
                <span x-show="! submitting">Upload asset</span>
                <span x-cloak x-show="submitting">Uploading...</span>
            </button>
        </form>
    </div>
</section>
