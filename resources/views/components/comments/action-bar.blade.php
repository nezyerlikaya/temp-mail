@props(['comment', 'canApprove', 'canMarkSpam', 'canTrashRestore'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    @if ($canApprove && $comment->status !== 'approved')
        <form method="POST" action="{{ route('admin.comment-moderation.approve', $comment) }}">
            @csrf
            <button class="inline-flex min-h-10 items-center rounded-lg bg-emerald-700 px-3 py-2 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-emerald-600/25">Approve</button>
        </form>
    @endif
    @if ($canMarkSpam && $comment->status !== 'spam')
        <form method="POST" action="{{ route('admin.comment-moderation.mark', $comment) }}">
            @csrf
            <input type="hidden" name="status" value="spam">
            <button class="inline-flex min-h-10 items-center rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-extrabold text-red-800 focus:outline-none focus:ring-4 focus:ring-red-600/20">Mark spam</button>
        </form>
    @endif
    @if ($canTrashRestore && $comment->status !== 'trashed')
        <form method="POST" action="{{ route('admin.comment-moderation.trash', $comment) }}" x-data="{ confirmTrash: false }">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button class="inline-flex min-h-10 items-center rounded-lg border border-stone-300 bg-stone-50 px-3 py-2 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-stone-400/25">Move to trash</button>
        </form>
    @endif
    @if ($canTrashRestore && $comment->status === 'trashed')
        <form method="POST" action="{{ route('admin.comment-moderation.restore', $comment) }}">
            @csrf
            <button class="inline-flex min-h-10 items-center rounded-lg border border-teal-300 bg-teal-50 px-3 py-2 text-sm font-extrabold text-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Restore</button>
        </form>
    @endif
</div>
