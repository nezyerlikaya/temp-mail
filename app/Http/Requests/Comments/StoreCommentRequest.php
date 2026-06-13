<?php

namespace App\Http\Requests\Comments;

use App\Models\Comment;
use App\Services\Comments\CommentContentSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', Rule::exists(Comment::class, 'id')],
            'author_name' => [Rule::requiredIf(fn (): bool => ! $this->user()), 'string', 'min:2', 'max:120'],
            'author_email' => [Rule::requiredIf(fn (): bool => ! $this->user()), 'email:rfc', 'max:190'],
            'content' => ['required', 'string', 'min:3', 'max:3000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (app(CommentContentSanitizer::class)->linkCount((string) $this->input('content')) > 3) {
                $validator->errors()->add('content', 'Comments can include at most three links.');
            }

            $parentId = $this->integer('parent_id');
            $post = $this->route('post');

            if ($parentId && $post) {
                $belongsToPost = Comment::query()
                    ->whereKey($parentId)
                    ->where('blog_post_id', $post->id)
                    ->exists();

                if (! $belongsToPost) {
                    $validator->errors()->add('parent_id', 'The selected parent comment does not belong to this post.');
                }
            }
        });
    }
}
