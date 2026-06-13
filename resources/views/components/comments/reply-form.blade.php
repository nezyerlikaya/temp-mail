@props(['comment', 'canReply'])

@if ($canReply && $comment->reply_depth === 0)
    <div x-data="{ open: false }" class="rounded-lg border border-stone-200 bg-stone-50 p-3">
        <button type="button" class="text-sm font-extrabold text-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()">
            Reply
        </button>
        <form x-show="open" x-cloak method="POST" action="{{ route('admin.comment-moderation.reply', $comment) }}" class="mt-3 space-y-3">
            @csrf
            <label for="reply-content-{{ $comment->id }}" class="block text-xs font-extrabold uppercase text-stone-500">Moderator reply</label>
            <textarea id="reply-content-{{ $comment->id }}" name="content" rows="3" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" required></textarea>
            <button class="inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/25">Post reply</button>
        </form>
    </div>
@endif
