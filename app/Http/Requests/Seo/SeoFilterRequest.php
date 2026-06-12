<?php

namespace App\Http\Requests\Seo;

use App\Models\Locale;
use App\Services\Seo\SeoTargetRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeoFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.view') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'locale' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === null || $value === '' || $value === 'all') {
                    return;
                }

                if (! Locale::query()->where('locale', (string) $value)->exists()) {
                    $fail('Choose a valid language filter.');
                }
            }],
            'target_type' => ['nullable', Rule::in(['all', ...app(SeoTargetRegistry::class)->keys()])],
            'missing_metadata' => ['nullable', Rule::in(['all', 'missing'])],
            'robots' => ['nullable', Rule::in(['all', 'index', 'noindex'])],
            'sitemap' => ['nullable', Rule::in(['all', 'included', 'excluded'])],
        ];
    }
}
