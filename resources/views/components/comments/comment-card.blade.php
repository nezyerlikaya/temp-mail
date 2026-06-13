@props([
    'comment',
    'canApprove',
    'canMarkSpam',
    'canReply',
    'canEdit',
    'canTrashRestore',
    'canDelete',
    'canManageBlocklist',
    'canViewPrivate',
])

<article {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <label class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-2.5 py-1 text-xs font-extrabold text-stone-700">
                    <input type="checkbox" value="{{ $comment->id }}" x-model="selected" class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/25">
                    Select
                </label>
                <x-comments.status-badge :status="$comment->status" />
                <x-comments.akismet-badge :provider="$comment->spam_provider" :decision="$comment->provider_decision" />
                <span class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">{{ $comment->locale?->locale }}</span>
                @if ($comment->manual_override)
                    <span class="rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-extrabold text-teal-800">{{ str($comment->manual_override)->headline() }}</span>
                @endif
            </div>
            <div>
                <p class="text-sm font-extrabold text-stone-950">{{ $comment->author_name }}</p>
                <p class="mt-1 text-xs font-semibold text-stone-500">{{ $comment->created_at?->format('M j, Y H:i') }} on {{ $comment->post?->title }}</p>
            </div>
            <div class="prose prose-sm max-w-none text-stone-700">{!! $comment->content !!}</div>
            <x-comments.reply-thread :comment="$comment" />
            @if ($canViewPrivate)
                <dl class="grid gap-3 text-xs sm:grid-cols-3">
                    <div>
                        <dt class="font-extrabold uppercase text-stone-500">Author email</dt>
                        <dd class="mt-1 font-semibold text-stone-800">{{ $comment->author_email ?: 'Registered user' }}</dd>
                    </div>
                    <div>
                        <dt class="font-extrabold uppercase text-stone-500">IP hash</dt>
                        <dd class="mt-1 truncate font-mono text-stone-800">{{ $comment->ip_hash ?: 'Unavailable' }}</dd>
                    </div>
                    <div>
                        <dt class="font-extrabold uppercase text-stone-500">User agent</dt>
                        <dd class="mt-1 font-semibold text-stone-800">Protected metadata ready</dd>
                    </div>
                </dl>
            @endif
            <x-comments.reply-form :comment="$comment" :can-reply="$canReply" />
            <x-comments.edit-form :comment="$comment" :can-edit="$canEdit" />
        </div>
        <div class="w-full shrink-0 space-y-4 lg:w-56">
            <x-comments.spam-score :score="$comment->spam_score" />
            <x-comments.action-bar :comment="$comment" :can-approve="$canApprove" :can-mark-spam="$canMarkSpam" :can-trash-restore="$canTrashRestore" />
            <x-comments.false-positive-panel :comment="$comment" :can-approve="$canApprove" />
            <x-comments.block-author-panel :comment="$comment" :can-manage-blocklist="$canManageBlocklist" :can-view-private="$canViewPrivate" />
            <x-comments.trash-warning :comment="$comment" :can-delete="$canDelete" />
        </div>
    </div>
</article>
