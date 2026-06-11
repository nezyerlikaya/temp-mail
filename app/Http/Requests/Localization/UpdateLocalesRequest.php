<?php

namespace App\Http\Requests\Localization;

use App\Models\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateLocalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.locale-launch-center.manage') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'locales' => ['required', 'array'],
            'locales.*.market_readiness' => ['required', 'in:planned,ready,blocked'],
            'locales.*.is_active' => ['sometimes', 'boolean'],
            'locales.*.is_default' => ['sometimes', 'boolean'],
            'locales.*.sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'locales.*.launch_status' => ['required', 'in:draft,ready,launched,paused'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $submitted = $this->input('locales', []);
                $validLocales = Locale::query()->get()->mapWithKeys(fn (Locale $locale): array => [
                    $locale->locale => [
                        'is_active' => $locale->is_active,
                        'is_default' => $locale->is_default,
                    ],
                ]);

                $locales = $validLocales->map(function (array $current, string $localeCode) use ($submitted): array {
                    if (! isset($submitted[$localeCode])) {
                        return $current;
                    }

                    return [
                        'is_active' => (bool) ($submitted[$localeCode]['is_active'] ?? false),
                        'is_default' => (bool) ($submitted[$localeCode]['is_default'] ?? false),
                    ];
                });

                if (collect($submitted)->contains(fn (array $locale): bool => (bool) ($locale['is_default'] ?? false))) {
                    $submittedDefault = collect($submitted)->filter(fn (array $locale): bool => (bool) ($locale['is_default'] ?? false));

                    if ($submittedDefault->count() > 1) {
                        $validator->errors()->add('locales', 'Exactly one default language is required.');

                        return;
                    }

                    $locales = $locales->map(fn (array $locale): array => [...$locale, 'is_default' => false]);

                    foreach ($submitted as $localeCode => $payload) {
                        if ((bool) ($payload['is_default'] ?? false)) {
                            $locales[$localeCode] = [
                                'is_active' => (bool) ($payload['is_active'] ?? false),
                                'is_default' => true,
                            ];
                        }
                    }
                }

                $defaults = $locales->filter(fn (array $locale): bool => (bool) ($locale['is_default'] ?? false));

                if ($defaults->count() !== 1) {
                    $validator->errors()->add('locales', 'Exactly one default language is required.');

                    return;
                }

                $default = $defaults->first();

                if (! (bool) ($default['is_active'] ?? false)) {
                    $validator->errors()->add('locales', 'The default language must be active.');
                }

                $validLocaleCodes = $validLocales->keys()->all();

                foreach (array_keys($submitted) as $localeCode) {
                    if (! in_array($localeCode, $validLocaleCodes, true)) {
                        $validator->errors()->add('locales', 'Unknown locale ['.$localeCode.'] cannot be updated.');
                    }
                }
            },
        ];
    }
}
