<?php

namespace App\Http\Requests\EmailTemplates;

use App\Services\EmailTemplates\EmailTemplateStore;
use Illuminate\Validation\Rule;

class UpdateEmailTemplateRequest extends StoreEmailTemplateRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.email-templates.update') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $template = $this->route('emailTemplate');

        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'template_key' => [
                'required',
                Rule::in(array_keys(app(EmailTemplateStore::class)->templateKeys())),
                Rule::unique('email_templates')
                    ->where(fn ($query) => $query->where('locale_id', $this->integer('locale_id')))
                    ->ignore($template?->id),
            ],
            'subject' => ['required', 'string', 'max:180'],
            'preheader' => ['nullable', 'string', 'max:240'],
            'html_body' => ['required', 'string', 'max:50000'],
            'plain_text_body' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', Rule::in(['draft', 'active', 'hidden'])],
        ];
    }
}
