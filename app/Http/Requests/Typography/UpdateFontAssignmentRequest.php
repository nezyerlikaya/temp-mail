<?php

namespace App\Http\Requests\Typography;

use App\Models\FontFamily;
use App\Models\Locale;
use App\Services\Themes\ThemeRegistry;
use App\Services\Typography\FontCoverageService;
use App\Services\Typography\FontRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateFontAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage font assignments') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(FontRegistry $registry): array
    {
        return [
            'scope' => ['required', Rule::in(['global', 'theme', 'locale'])],
            'scope_key' => ['required', 'string', 'max:80'],
            'assignments' => ['required', 'array'],
            'assignments.*.font_family_slug' => ['required', 'string', Rule::exists('font_families', 'slug')->where('is_active', true)],
            'assignments.*.fallback_stack' => ['nullable', 'array', 'max:6'],
            'assignments.*.fallback_stack.*' => ['string', 'max:80', Rule::in([...$registry->slugs(), ...$registry->safeFallbacks()])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $themes = app(ThemeRegistry::class);
                $registry = app(FontRegistry::class);
                $coverage = app(FontCoverageService::class);

                $scope = (string) $this->input('scope');
                $scopeKey = (string) $this->input('scope_key');

                if ($scope === 'global' && $scopeKey !== 'default') {
                    $validator->errors()->add('scope_key', 'Global typography assignments must use the default scope.');
                }

                if ($scope === 'theme' && ! $themes->exists($scopeKey)) {
                    $validator->errors()->add('scope_key', 'Choose a registered public theme.');
                }

                $locale = null;
                if ($scope === 'locale') {
                    $locale = Locale::query()->where('locale', $scopeKey)->first();
                    if (! $locale) {
                        $validator->errors()->add('scope_key', 'Choose a registered locale.');
                    }
                }

                $submittedUsages = array_keys((array) $this->input('assignments', []));
                $unknownUsages = array_diff($submittedUsages, array_keys($registry->usageScopes()));
                if ($unknownUsages !== []) {
                    $validator->errors()->add('assignments', 'Only UI, heading, body, and mono font scopes can be assigned.');
                }

                if (! $locale) {
                    return;
                }

                foreach ((array) $this->input('assignments', []) as $usage => $assignment) {
                    $family = FontFamily::query()->where('slug', $assignment['font_family_slug'] ?? null)->first();
                    if (! $family) {
                        continue;
                    }

                    foreach ($coverage->warningsForAssignment($family, $locale) as $warning) {
                        if ($warning['level'] === 'warning') {
                            $validator->errors()->add('assignments.'.$usage.'.font_family_slug', $warning['message']);
                        }
                    }
                }
            },
        ];
    }
}
