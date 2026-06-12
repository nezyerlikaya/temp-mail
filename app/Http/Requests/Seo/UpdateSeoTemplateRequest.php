<?php

namespace App\Http\Requests\Seo;

use App\Services\Seo\SeoSchemaValidator;
use App\Services\Seo\SeoTargetRegistry;
use App\Services\Seo\SeoTemplateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateSeoTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.templates') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'locale_id' => $this->input('locale_id') ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'target_type' => ['required', 'string', Rule::in(app(SeoTargetRegistry::class)->keys())],
            'locale_id' => ['nullable', 'integer', 'exists:locales,id'],
            'name' => ['required', 'string', 'max:120'],
            'meta_title_template' => ['nullable', 'string', 'max:180'],
            'meta_description_template' => ['nullable', 'string', 'max:320'],
            'og_title_template' => ['nullable', 'string', 'max:180'],
            'og_description_template' => ['nullable', 'string', 'max:320'],
            'schema_type' => ['nullable', 'string', 'max:80'],
            'schema_json_text' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null)
    {
        $payload = parent::validated($key, $default);

        if ($key !== null) {
            return $payload;
        }

        $invalid = app(SeoTemplateService::class)->invalidVariables($payload);
        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'meta_title_template' => 'Unsupported SEO template variables: '.implode(', ', $invalid).'.',
            ]);
        }

        $payload['schema_json_template'] = app(SeoSchemaValidator::class)->sanitize($payload['schema_json_text'] ?? null);
        unset($payload['schema_json_text']);

        return $payload;
    }
}
