<?php

namespace App\Http\Requests\EmailTemplates;

use App\Services\EmailTemplates\EmailTemplateSanitizer;
use App\Services\EmailTemplates\EmailTemplateStore;
use App\Services\EmailTemplates\EmailTemplateVariableRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.email-templates.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'template_key' => [
                'required',
                Rule::in(array_keys(app(EmailTemplateStore::class)->templateKeys())),
                Rule::unique('email_templates')->where(fn ($query) => $query->where('locale_id', $this->integer('locale_id'))),
            ],
            'subject' => ['required', 'string', 'max:180'],
            'preheader' => ['nullable', 'string', 'max:240'],
            'html_body' => ['required', 'string', 'max:50000'],
            'plain_text_body' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', Rule::in(['draft', 'active', 'hidden'])],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null)
    {
        $payload = parent::validated($key, $default);

        if ($key !== null) {
            return $payload;
        }

        return $this->safePayload($payload);
    }

    /** @param array<string, mixed> $payload */
    protected function safePayload(array $payload): array
    {
        $content = implode("\n", [
            $payload['subject'] ?? '',
            $payload['preheader'] ?? '',
            $payload['html_body'] ?? '',
            $payload['plain_text_body'] ?? '',
        ]);
        $variables = app(EmailTemplateVariableRegistry::class);
        $invalid = $variables->invalidVariables($content);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'html_body' => 'Unsupported email variables: '.implode(', ', $invalid).'.',
            ]);
        }

        if (($payload['status'] ?? 'draft') === 'active') {
            $missing = $variables->missingRequired((string) $payload['template_key'], $content);
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'html_body' => 'Active critical templates must include: '.implode(', ', $missing).'.',
                ]);
            }
        }

        $sanitized = app(EmailTemplateSanitizer::class)->sanitize(
            (string) $payload['html_body'],
            $payload['plain_text_body'] ?? null,
        );

        $payload['html_body'] = $sanitized['html'];
        $payload['plain_text_body'] = $sanitized['plain'];

        return $payload;
    }
}
