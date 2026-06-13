<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlockCommentAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage comment blocklist') ?? false;
    }

    public function rules(): array
    {
        return ['type' => ['required', Rule::in(['email', 'ip'])]];
    }
}
