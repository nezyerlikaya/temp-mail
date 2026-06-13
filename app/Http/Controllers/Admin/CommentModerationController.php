<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Comments\ApproveCommentAction;
use App\Actions\Comments\MarkCommentSpamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\ApproveCommentRequest;
use App\Http\Requests\Comments\CommentFilterRequest;
use App\Http\Requests\Comments\MarkCommentSpamRequest;
use App\Models\Comment;
use App\Services\Comments\CommentModerationService;
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
            'locales' => $moderation->locales(),
            'canApprove' => $request->user()?->can('approve comments') ?? false,
            'canMarkSpam' => $request->user()?->can('mark comments as spam') ?? false,
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
}
