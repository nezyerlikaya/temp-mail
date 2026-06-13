<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkCommentSpamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('mark comments as spam') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['spam', 'trashed'])],
        ];
    }
}
