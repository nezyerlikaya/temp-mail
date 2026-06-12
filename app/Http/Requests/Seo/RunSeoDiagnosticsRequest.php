<?php

namespace App\Http\Requests\Seo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunSeoDiagnosticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.diagnostics') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'severity' => ['nullable', Rule::in(['all', 'critical', 'warning', 'notice'])],
            'issue' => ['nullable', Rule::in([
                'all',
                'missing_metadata',
                'duplicate_title',
                'duplicate_description',
                'missing_og_image',
                'invalid_canonical',
                'canonical_locale_conflict',
                'noindex_risk',
                'missing_schema',
                'invalid_schema',
                'slug_conflict',
                'hreflang_canonical_conflict',
            ])],
        ];
    }
}
