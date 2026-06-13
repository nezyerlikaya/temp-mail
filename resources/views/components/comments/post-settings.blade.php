@props(['posts', 'canUpdateSettings'])

@if ($canUpdateSettings)
    <details class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer text-base font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Per-post comment controls</summary>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @foreach ($posts as $post)
                <form method="POST" action="{{ route('admin.comment-moderation.posts.settings', $post->id) }}" class="rounded-lg border border-stone-200 p-4">
                    @csrf
                    @method('PUT')
                    <p class="truncate text-sm font-extrabold text-stone-950">{{ $post->title }}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                            <input type="checkbox" name="comments_enabled" value="1" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/25" @checked($post->comments_enabled)> Enabled
                        </label>
                        <label class="flex items-center gap-2 text-xs font-bold text-stone-700">
                            <input type="checkbox" name="comments_moderation_required" value="1" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/25" @checked($post->comments_moderation_required)> Moderate
                        </label>
                        <input type="date" name="comments_closed_at" value="{{ $post->comments_closed_at?->format('Y-m-d') }}" class="min-h-9 rounded-lg border border-stone-300 px-2 text-xs font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" aria-label="Close comments date for {{ $post->title }}">
                    </div>
                    <button class="mt-3 inline-flex min-h-9 items-center rounded-lg border border-stone-300 px-3 text-xs font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Save post controls</button>
                </form>
            @endforeach
        </div>
    </details>
@endif
