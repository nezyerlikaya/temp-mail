<?php

namespace App\Services\Localization;

use App\Models\Locale;
use App\Services\Translations\TranslationCoverageService;
use Illuminate\Support\Collection;

class LocaleReadinessService
{
    public function __construct(private readonly TranslationCoverageService $translationCoverage) {}

    /**
     * @param  Collection<int, Locale>  $locales
     * @return array{total: int, active: int, passive: int, ready: int, launched: int, live: int, in_review: int, draft: int, offline: int, rtl: int, average_translation_coverage: int, default_locale: string|null, issues: array<int, string>}
     */
    public function summary(Collection $locales): array
    {
        $default = $locales->firstWhere('is_default', true);
        $issues = [];

        if ($locales->where('is_default', true)->count() !== 1) {
            $issues[] = 'Exactly one default language is required.';
        }

        if ($default instanceof Locale && ! $default->is_active) {
            $issues[] = 'The default language must be active.';
        }

        if ($locales->where('direction', 'rtl')->whereNotIn('locale', ['ar', 'he'])->count() > 0) {
            $issues[] = 'Only validated RTL locales should use right-to-left direction.';
        }

        return [
            'total' => $locales->count(),
            'active' => $locales->where('is_active', true)->count(),
            'passive' => $locales->where('is_active', false)->count(),
            'ready' => $locales->where('market_readiness', 'ready')->count(),
            'launched' => $locales->where('launch_status', 'launched')->count(),
            'live' => $locales->where('launch_status', 'launched')->count(),
            'in_review' => $locales->where('launch_status', 'ready')->count(),
            'draft' => $locales->where('launch_status', 'draft')->count(),
            'offline' => $locales->where('launch_status', 'paused')->count() + $locales->where('is_active', false)->count(),
            'rtl' => $locales->where('direction', 'rtl')->count(),
            'average_translation_coverage' => (int) round($locales->avg(fn (Locale $locale): int => $this->forLocale($locale)['categories']['copy_ui_text']['score']) ?: 0),
            'default_locale' => $default?->locale,
            'issues' => $issues,
        ];
    }

    /** @return array{score: int, label: string, display_status: string, missing: array<int, string>, categories: array<string, array{label: string, score: int, status: string}>} */
    public function forLocale(Locale $locale): array
    {
        $categories = $this->categories($locale);

        $score = (int) round(collect($categories)->avg('score'));

        return [
            'score' => $score,
            'label' => match (true) {
                $score >= 100 => 'Launch ready',
                $score >= 70 => 'In review',
                default => 'Planned',
            },
            'display_status' => $this->displayStatus($locale),
            'missing' => collect($categories)
                ->filter(fn (array $category): bool => $category['score'] < 70)
                ->pluck('label')
                ->values()
                ->all(),
            'categories' => $categories,
        ];
    }

    public function displayStatus(Locale $locale): string
    {
        return match (true) {
            $locale->launch_status === 'launched' && $locale->is_active => 'Live',
            $locale->launch_status === 'ready' && $locale->is_active => 'In Review',
            $locale->launch_status === 'paused' || ! $locale->is_active => 'Offline',
            default => 'Draft',
        };
    }

    /** @return array<string, array{label: string, score: int, status: string}> */
    private function categories(Locale $locale): array
    {
        $stored = is_array($locale->readiness) ? $locale->readiness : [];

        $scores = [
            'copy_ui_text' => $this->translationCoverage->forLocale($locale)['coverage'],
            'content' => $stored['content'] ?? match ($locale->launch_status) {
                'launched' => 88,
                'ready' => 74,
                default => 38,
            },
            'seo' => $stored['seo'] ?? ($locale->launch_status === 'launched' ? 86 : ($locale->market_readiness === 'ready' ? 68 : 28)),
            'mailbox_experience' => $stored['mailbox_experience'] ?? ($locale->is_active ? 78 : 40),
            'transactional_emails' => $stored['transactional_emails'] ?? ($locale->is_active ? 76 : 35),
            'compliance' => $stored['compliance'] ?? (in_array($locale->region, ['DACH', 'France', 'Italy', 'Netherlands', 'Poland', 'Nordics', 'Norway', 'Denmark', 'Finland', 'Czechia', 'Hungary', 'Romania', 'Greece'], true) ? 72 : 58),
        ];

        return collect([
            'copy_ui_text' => 'Copy & UI Text coverage',
            'content' => 'Content coverage',
            'seo' => 'SEO readiness',
            'mailbox_experience' => 'Mailbox Experience readiness',
            'transactional_emails' => 'Transactional Emails readiness',
            'compliance' => 'Compliance readiness',
        ])->mapWithKeys(fn (string $label, string $key): array => [
            $key => [
                'label' => $label,
                'score' => (int) $scores[$key],
                'status' => $scores[$key] >= 85 ? 'ready' : ($scores[$key] >= 70 ? 'review' : 'missing'),
            ],
        ])->all();
    }
}
