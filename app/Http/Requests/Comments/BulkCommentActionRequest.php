<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkCommentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('moderate comments') ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'spam', 'trash', 'restore'])],
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['integer', 'exists:comments,id'],
        ];
    }
}
