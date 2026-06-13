<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class TrashCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('trash restore comments') ?? false;
    }

    public function rules(): array
    {
        return ['confirm' => ['nullable', 'accepted']];
    }
}
