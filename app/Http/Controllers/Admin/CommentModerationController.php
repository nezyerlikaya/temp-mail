<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Comments\ApproveCommentAction;
use App\Actions\Comments\BulkCommentAction;
use App\Actions\Comments\CommentReplyAction;
use App\Actions\Comments\DeleteCommentAction;
use App\Actions\Comments\EditCommentAction;
use App\Actions\Comments\MarkCommentSpamAction;
use App\Actions\Comments\RestoreCommentAction;
use App\Actions\Comments\RestoreSpamFalsePositiveAction;
use App\Actions\Comments\TrashCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\ApproveCommentRequest;
use App\Http\Requests\Comments\BlockCommentAuthorRequest;
use App\Http\Requests\Comments\BulkCommentActionRequest;
use App\Http\Requests\Comments\CommentFilterRequest;
use App\Http\Requests\Comments\DeleteCommentRequest;
use App\Http\Requests\Comments\EditCommentRequest;
use App\Http\Requests\Comments\MarkCommentSpamRequest;
use App\Http\Requests\Comments\ReplyCommentRequest;
use App\Http\Requests\Comments\RestoreCommentRequest;
use App\Http\Requests\Comments\TrashCommentRequest;
use App\Http\Requests\Comments\UpdateCommentSettingsRequest;
use App\Http\Requests\Comments\UpdatePostCommentSettingsRequest;
use App\Models\BlogPost;
use App\Models\Comment;
use App\Services\Audit\AuditLogger;
use App\Services\Comments\CommentBlocklistService;
use App\Services\Comments\CommentModerationService;
use App\Services\Comments\CommentSettingsStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentModerationController extends Controller
{
    public function index(CommentFilterRequest $request, CommentModerationService $moderation): View
    {
        $filters = $request->filters();

        return view('dashboard.comment-moderation.index', [
            'adminUser' => $request->user(),
            'comments' => $moderation->queue($filters),
            'summary' => $moderation->summary(),
            'filters' => $filters,
            'posts' => $moderation->posts(),
            'postControls' => $moderation->postControls(),
            'locales' => $moderation->locales(),
            'settings' => app(CommentSettingsStore::class)->settings(),
            'canApprove' => $request->user()?->can('approve comments') ?? false,
            'canMarkSpam' => $request->user()?->can('mark comments as spam') ?? false,
            'canReply' => $request->user()?->can('reply to comments') ?? false,
            'canEdit' => $request->user()?->can('edit comments') ?? false,
            'canTrashRestore' => $request->user()?->can('trash restore comments') ?? false,
            'canDelete' => $request->user()?->can('permanently delete comments') ?? false,
            'canManageBlocklist' => $request->user()?->can('manage comment blocklist') ?? false,
            'canUpdateSettings' => $request->user()?->can('update comment settings') ?? false,
            'canViewPrivate' => $request->user()?->can('moderate comments') ?? false,
        ]);
    }

    public function approve(ApproveCommentRequest $request, Comment $comment, ApproveCommentAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment);

        return back()->with('status', 'Comment approved.');
    }

    public function mark(MarkCommentSpamRequest $request, Comment $comment, MarkCommentSpamAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment, $request->validated('status'));

        return back()->with('status', $request->validated('status') === 'trashed' ? 'Comment moved to trash.' : 'Comment marked as spam.');
    }

    public function reply(ReplyCommentRequest $request, Comment $comment, CommentReplyAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment, $request->validated(), $request);

        return back()->with('status', 'Reply added.');
    }

    public function edit(EditCommentRequest $request, Comment $comment, EditCommentAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment, $request->validated());

        return back()->with('status', 'Comment updated.');
    }

    public function trash(TrashCommentRequest $request, Comment $comment, TrashCommentAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment);

        return back()->with('status', 'Comment moved to trash.');
    }

    public function restore(RestoreCommentRequest $request, Comment $comment, RestoreCommentAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment);

        return back()->with('status', 'Comment restored.');
    }

    public function destroy(DeleteCommentRequest $request, Comment $comment, DeleteCommentAction $action): RedirectResponse
    {
        $action->handle($request->user(), $comment);

        return back()->with('status', 'Comment permanently deleted.');
    }

    public function falsePositive(ApproveCommentRequest $request, Comment $comment, RestoreSpamFalsePositiveAction $action): RedirectResponse
    {
        $status = in_array($request->input('status'), ['pending', 'approved'], true)
            ? (string) $request->input('status')
            : 'pending';

        $action->handle($request->user(), $comment, $status);

        return back()->with('status', 'Spam false positive restored.');
    }

    public function bulk(BulkCommentActionRequest $request, BulkCommentAction $action): RedirectResponse
    {
        $count = $action->handle($request->user(), $request->validated('action'), $request->validated('comment_ids'));

        return back()->with('status', "{$count} comments updated.");
    }

    public function block(BlockCommentAuthorRequest $request, Comment $comment, CommentBlocklistService $blocklist): RedirectResponse
    {
        $blocklist->block($request->user(), $comment, $request->validated('type'));

        return back()->with('status', 'Author block readiness added.');
    }

    public function settings(UpdateCommentSettingsRequest $request, CommentSettingsStore $settings, AuditLogger $audit): RedirectResponse
    {
        $settings->update($request->settings(), $request->user());
        $audit->record('comment.settings_changed', $request->user(), null, [
            'changed_keys' => array_keys($request->settings()),
            'maximum_reply_depth' => 1,
        ], ['module' => 'content']);

        return back()->with('status', 'Comment settings updated.');
    }

    public function postSettings(UpdatePostCommentSettingsRequest $request, BlogPost $post, AuditLogger $audit): RedirectResponse
    {
        $post->forceFill([
            'comments_enabled' => $request->boolean('comments_enabled'),
            'comments_closed_at' => $request->input('comments_closed_at') ?: null,
            'comments_moderation_required' => $request->boolean('comments_moderation_required'),
        ])->save();

        $audit->record('comment.post_settings_changed', $request->user(), null, [
            'post_id' => $post->id,
            'comments_enabled' => $post->comments_enabled,
            'comments_moderation_required' => $post->comments_moderation_required,
        ], ['module' => 'content', 'target' => $post]);

        return back()->with('status', 'Post comment controls updated.');
    }
}
