<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostCommentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update comment settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'comments_enabled' => ['nullable', 'boolean'],
            'comments_closed_at' => ['nullable', 'date'],
            'comments_moderation_required' => ['nullable', 'boolean'],
        ];
    }
}
