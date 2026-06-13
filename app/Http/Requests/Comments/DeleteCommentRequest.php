<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permanently delete comments') ?? false;
    }

    public function rules(): array
    {
        return ['confirm_delete' => ['accepted']];
    }
}
