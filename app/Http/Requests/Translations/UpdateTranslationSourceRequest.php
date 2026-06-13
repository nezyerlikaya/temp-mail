<?php

namespace App\Http\Requests\Translations;

use App\Models\TranslationSource;
use Illuminate\Validation\Rule;

class UpdateTranslationSourceRequest extends StoreTranslationSourceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update translation sources') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $source = $this->route('translationSource');
        $sourceId = $source instanceof TranslationSource ? $source->id : null;

        $rules = parent::rules();
        $rules['translation_key'] = ['required', 'string', 'max:180', 'regex:/^[a-z][a-z0-9]*(\.[a-z][a-z0-9]*)+$/', Rule::unique('translation_sources', 'translation_key')->ignore($sourceId)];

        return $rules;
    }
}
