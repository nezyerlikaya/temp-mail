<?php

namespace App\Http\Requests\Comments;

use App\Services\Comments\CommentContentSanitizer;
use App\Services\Comments\CommentSettingsStore;
use Illuminate\Foundation\Http\FormRequest;

class EditCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit comments') ?? false;
    }

    public function rules(): array
    {
        $settings = app(CommentSettingsStore::class)->settings();

        return ['content' => ['required', 'string', 'min:'.(int) $settings['minimum_length'], 'max:'.(int) $settings['maximum_length']]];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! app(CommentContentSanitizer::class)->isSafe((string) $this->input('content'))) {
                $validator->errors()->add('content', 'Comments cannot include executable content.');
            }
        });
    }
}
