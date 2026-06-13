<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('approve comments') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
