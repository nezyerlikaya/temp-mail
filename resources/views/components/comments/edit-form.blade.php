@props(['comment', 'canEdit'])

@if ($canEdit)
    <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-white p-3">
        <button type="button" class="text-sm font-extrabold text-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()">
            Edit comment
        </button>
        <form x-show="open" x-cloak method="POST" action="{{ route('admin.comment-moderation.edit', $comment) }}" class="mt-3 space-y-3">
            @csrf
            @method('PUT')
            <label for="edit-content-{{ $comment->id }}" class="block text-xs font-extrabold uppercase text-stone-500">Comment content</label>
            <textarea id="edit-content-{{ $comment->id }}" name="content" rows="4" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" required>{{ strip_tags($comment->content) }}</textarea>
            <button class="inline-flex min-h-10 items-center rounded-lg bg-stone-950 px-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-stone-500/25">Save edit</button>
        </form>
        @if ($comment->edited_at)
            <p class="mt-2 text-xs font-bold text-stone-500">Edited {{ $comment->edited_at->format('M j, Y H:i') }}. History entries: {{ $comment->editHistories->count() }}</p>
        @endif
    </div>
@endif
