<?php

namespace App\Http\Requests\Sections;

use App\Models\Locale;
use App\Services\Sections\SectionPlacementRegistry;
use App\Services\Sections\SectionStore;
use App\Services\Sections\SectionTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.sections-studio.view') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'locale_id' => ['nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' || $value === null || $value === '') {
                    return;
                }

                if (! ctype_digit((string) $value) || ! Locale::query()->whereKey((int) $value)->exists()) {
                    $fail('Choose a valid language filter.');
                }
            }],
            'section_type' => ['nullable', Rule::in(['all', ...app(SectionTypeRegistry::class)->keys()])],
            'placement' => ['nullable', Rule::in(['all', ...app(SectionPlacementRegistry::class)->keys()])],
            'status' => ['nullable', Rule::in(['all', ...array_keys(app(SectionStore::class)->statuses())])],
        ];
    }
}
