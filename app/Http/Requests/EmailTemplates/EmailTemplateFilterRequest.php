<?php

namespace App\Http\Requests\EmailTemplates;

use App\Services\EmailTemplates\EmailTemplateStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailTemplateFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.email-templates.view') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale_id' => ['nullable'],
            'template_key' => ['nullable', Rule::in(['all', ...array_keys(app(EmailTemplateStore::class)->templateKeys())])],
            'status' => ['nullable', Rule::in(['all', 'draft', 'active', 'hidden'])],
            'missing' => ['nullable', Rule::in(['all', 'missing'])],
        ];
    }
}
