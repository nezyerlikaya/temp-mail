@props(['post' => null, 'statuses' => [], 'selectedStatus' => 'draft', 'canPublish' => false, 'canHide' => false])

<aside class="min-w-0 space-y-4">
    <x-admin.card title="Publish panel" description="Save the draft, publish, or hide this language-specific post.">
        <div class="space-y-4">
            <div>
                <label for="blog-status" class="text-sm font-extrabold text-stone-950">Status</label>
                <select id="blog-status" name="status" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('status') aria-invalid="true" aria-describedby="blog-status-error" @enderror>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p id="blog-status-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="blog-published-at" class="text-sm font-extrabold text-stone-950">Published at</label>
                <input
                    id="blog-published-at"
                    name="published_at"
                    type="datetime-local"
                    value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}"
                    class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                    @error('published_at') aria-invalid="true" aria-describedby="blog-published-at-error" @enderror
                >
                @error('published_at')
                    <p id="blog-published-at-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-lg border border-stone-200 bg-stone-50 p-3 text-sm">
                <p class="text-xs font-bold uppercase text-stone-500">Current state</p>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <x-blog.status-badge :status="$selectedStatus" />
                    <span class="text-xs font-bold text-stone-500">{{ $post?->updated_at?->diffForHumans() ?? 'Not saved yet' }}</span>
                </div>
            </div>

            <div class="grid gap-2">
                <button type="submit" name="intent" value="save_draft" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="intent = 'save_draft'">
                    <span x-show="! submitting || intent !== 'save_draft'">Save draft</span>
                    <span x-cloak x-show="submitting && intent === 'save_draft'">Saving...</span>
                </button>
                <button type="submit" name="intent" value="publish" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" x-on:click="intent = 'publish'" @disabled(! $canPublish)>
                    <span x-show="! submitting || intent !== 'publish'">Publish</span>
                    <span x-cloak x-show="submitting && intent === 'publish'">Publishing...</span>
                </button>
                <button type="submit" name="intent" value="hide" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-stone-400/20 disabled:cursor-not-allowed disabled:opacity-60" x-on:click="intent = 'hide'" @disabled(! $canHide)>
                    <span x-show="! submitting || intent !== 'hide'">Hide</span>
                    <span x-cloak x-show="submitting && intent === 'hide'">Hiding...</span>
                </button>
            </div>
        </div>
    </x-admin.card>

    <x-admin.card title="Post ownership" description="Author and readiness context for this record.">
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="text-xs font-bold uppercase text-stone-500">Author</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ $post?->author?->name ?? 'Current admin' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-bold uppercase text-stone-500">Language</dt>
                <dd class="mt-1">{{ $post?->locale ? '' : 'Selected in editor' }}@if ($post?->locale)<x-blog.language-badge :locale="$post->locale" />@endif</dd>
            </div>
            <div>
                <dt class="text-xs font-bold uppercase text-stone-500">Featured media</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ $post?->featured_media_id ? '#'.$post->featured_media_id : 'Not selected' }}</dd>
            </div>
        </dl>
    </x-admin.card>
</aside>
