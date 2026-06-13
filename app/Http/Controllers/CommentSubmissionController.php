<?php

namespace App\Http\Controllers;

use App\Actions\Comments\SubmitCommentAction;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;

class CommentSubmissionController extends Controller
{
    public function store(StoreCommentRequest $request, BlogPost $post, SubmitCommentAction $action): RedirectResponse
    {
        $comment = $action->handle($post, $request->validated(), $request, $request->user());

        return $this->response($comment->status);
    }

    public function storeLocalized(StoreCommentRequest $request, string $locale, BlogPost $post, SubmitCommentAction $action): RedirectResponse
    {
        $comment = $action->handle($post, $request->validated(), $request, $request->user());

        return $this->response($comment->status);
    }

    private function response(string $status): RedirectResponse
    {
        return back()->with('status', $status === 'spam'
            ? 'Comment received and queued for moderation.'
            : 'Comment submitted and waiting for moderation.');
    }
}
