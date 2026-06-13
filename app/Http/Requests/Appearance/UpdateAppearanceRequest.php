<?php

namespace App\Http\Requests\Appearance;

use App\Services\Appearance\AppearanceTokenRegistry;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update appearance') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $registry = app(AppearanceTokenRegistry::class);

        $rules = [
            'theme' => ['required', 'string', Rule::in(app(ThemeRegistry::class)->slugs())],
            'mode' => ['required', 'string', Rule::in(['defaults', 'custom'])],
            'tokens' => ['required', 'array'],
        ];

        foreach ($registry->tokens() as $name => $token) {
            $rules['tokens.'.$name] = match ($token['type']) {
                'color' => ['required', 'string', 'regex:'.AppearanceTokenRegistry::COLOR_PATTERN],
                'radius' => ['required', 'string', Rule::in(array_keys($registry->radiusOptions()))],
                'shadow' => ['required', 'string', Rule::in(array_keys($registry->shadowOptions()))],
                'motion' => ['required', 'string', Rule::in(array_keys($registry->motionOptions()))],
                default => ['prohibited'],
            };
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $allowed = app(AppearanceTokenRegistry::class)->tokenNames();
            $tokens = array_keys((array) $this->input('tokens', []));

            if (array_diff($tokens, $allowed) !== []) {
                $validator->errors()->add('tokens', 'Only allowlisted appearance tokens can be saved.');
            }
        });
    }
}
