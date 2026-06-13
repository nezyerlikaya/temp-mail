<?php

namespace App\Http\Requests\Comments;

use App\Models\Comment;
use App\Services\Comments\CommentContentSanitizer;
use App\Services\Comments\CommentSettingsStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function rules(): array
    {
        $settings = app(CommentSettingsStore::class)->settings();

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists(Comment::class, 'id')],
            'author_name' => [Rule::requiredIf(fn (): bool => ! $this->user()), 'string', 'min:2', 'max:120'],
            'author_email' => [Rule::requiredIf(fn (): bool => ! $this->user()), 'email:rfc', 'max:190'],
            'content' => ['required', 'string', 'min:'.(int) $settings['minimum_length'], 'max:'.(int) $settings['maximum_length']],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $settings = app(CommentSettingsStore::class)->settings();
            if (app(CommentContentSanitizer::class)->linkCount((string) $this->input('content')) > (int) $settings['maximum_links']) {
                $validator->errors()->add('content', 'Comments include too many links.');
            }

            foreach ($settings['blocked_words'] ?? [] as $word) {
                if (filled($word) && str_contains(strtolower((string) $this->input('content')), strtolower((string) $word))) {
                    $validator->errors()->add('content', 'This comment includes blocked words.');
                    break;
                }
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
