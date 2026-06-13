<x-comments.moderation-layout :user="$adminUser">
    <x-admin.page-header
        eyebrow="Content"
        title="Comment Moderation"
        description="Review blog comments, spam signals, and protected author metadata without editing Blog Studio content."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Moderation queue" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Comment moderation summary">
        <x-comments.metric-card label="Pending review" :value="$summary['pending']" description="Awaiting moderator action" />
        <x-comments.metric-card label="Approved" :value="$summary['approved']" description="Visible after approval" />
        <x-comments.metric-card label="Spam" :value="$summary['spam']" description="Spam decisions and flags" />
        <x-comments.metric-card label="Trashed" :value="$summary['trashed']" description="Trash readiness queue" />
        <x-comments.metric-card label="Comments today" :value="$summary['today']" description="New submissions" />
    </section>

    <div class="space-y-5">
        <x-comments.queue-tabs :filters="$filters" :summary="$summary" />
        <x-comments.filter-bar :filters="$filters" :posts="$posts" :locales="$locales" />

        @if ($comments->count() > 0)
            <div class="space-y-4">
                @foreach ($comments as $comment)
                    <x-comments.comment-card :comment="$comment" :can-approve="$canApprove" :can-mark-spam="$canMarkSpam" :can-view-private="$canViewPrivate" />
                @endforeach
            </div>
            <x-admin.pagination :paginator="$comments" />
        @else
            <x-comments.empty-state />
        @endif
    </div>
</x-comments.moderation-layout>
