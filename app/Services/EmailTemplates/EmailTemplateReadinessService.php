<?php

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Locale;
use Illuminate\Support\Collection;

class EmailTemplateReadinessService
{
    public function __construct(
        private readonly EmailTemplateStore $store,
        private readonly EmailTemplateVariableRegistry $variables,
    ) {}

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $locales = $this->store->locales();
        $keys = $this->store->templateKeys();
        $templates = EmailTemplate::query()->with('locale')->get();
        $expected = max(1, $locales->count() * count($keys));
        $active = $templates->where('status', 'active')->count();

        return [
            'score' => (int) round(($active / $expected) * 100),
            'active' => $active,
            'expected' => $expected,
            'languages' => $this->languageCoverage($locales, $templates, count($keys)),
            'missing' => $this->store->missingQueue(),
        ];
    }

    /** @return array<string, mixed> */
    public function template(EmailTemplate $template): array
    {
        $content = implode("\n", [
            $template->subject,
            $template->preheader,
            $template->html_body,
            $template->plain_text_body,
        ]);
        $missingRequired = $this->variables->missingRequired($template->template_key, $content);
        $warnings = [];

        if (mb_strlen($template->subject) > 78) {
            $warnings[] = ['type' => 'subject_length', 'message' => 'Subject is longer than 78 characters.'];
        }

        if (preg_match('/free|urgent|winner|act now|guaranteed/i', $template->subject) === 1) {
            $warnings[] = ['type' => 'spammy_subject', 'message' => 'Subject contains wording that may look promotional or spammy.'];
        }

        if (blank($template->preheader)) {
            $warnings[] = ['type' => 'missing_preheader', 'message' => 'Preheader is missing.'];
        }

        if (blank($template->plain_text_body)) {
            $warnings[] = ['type' => 'missing_plain_text', 'message' => 'Plain-text fallback is missing.'];
        }

        if ($missingRequired !== []) {
            $warnings[] = ['type' => 'missing_required_variables', 'message' => 'Missing required variables: '.implode(', ', $missingRequired).'.'];
        }

        return [
            'warnings' => $warnings,
            'missing_required' => $missingRequired,
            'score' => max(0, 100 - (count($warnings) * 20)),
        ];
    }

    /**
     * @param  Collection<int, Locale>  $locales
     * @param  Collection<int, EmailTemplate>  $templates
     * @return Collection<int, array<string, mixed>>
     */
    private function languageCoverage(Collection $locales, Collection $templates, int $keyCount): Collection
    {
        return $locales->map(function (Locale $locale) use ($templates, $keyCount): array {
            $localeTemplates = $templates->where('locale_id', $locale->id);
            $active = $localeTemplates->where('status', 'active')->count();

            return [
                'locale' => $locale,
                'active' => $active,
                'expected' => $keyCount,
                'score' => $keyCount > 0 ? (int) round(($active / $keyCount) * 100) : 0,
            ];
        });
    }
}
