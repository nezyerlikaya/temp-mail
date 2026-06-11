<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublishPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('intent')) {
            'publish' => $this->user()?->can('admin.page-studio.publish') ?? false,
            'hide' => $this->user()?->can('admin.page-studio.hide') ?? false,
            default => $this->user()?->can('admin.page-studio.update') ?? false,
        };
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'intent' => ['required', Rule::in(['save_draft', 'publish', 'hide'])],
        ];
    }
}
