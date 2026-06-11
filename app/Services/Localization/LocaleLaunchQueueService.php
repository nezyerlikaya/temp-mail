<?php

namespace App\Services\Localization;

use App\Models\Locale;
use Illuminate\Support\Collection;

class LocaleLaunchQueueService
{
    public function __construct(private readonly LocaleReadinessService $readiness) {}

    /**
     * @param  Collection<int, Locale>  $locales
     * @return array<string, array{label: string, description: string, count: int, locales: array<int, string>}>
     */
    public function build(Collection $locales): array
    {
        return [
            'ready_for_launch' => $this->queue($locales, 'Ready for launch', 'Active locales with strong overall readiness.', fn (Locale $locale): bool => $this->readiness->forLocale($locale)['score'] >= 80 && $locale->is_active),
            'missing_seo' => $this->queue($locales, 'Missing SEO metadata', 'Markets that need SEO Growth Center work before launch.', fn (Locale $locale): bool => $this->readiness->forLocale($locale)['categories']['seo']['score'] < 70),
            'missing_email_templates' => $this->queue($locales, 'Missing email templates', 'Transactional email readiness needs attention.', fn (Locale $locale): bool => $this->readiness->forLocale($locale)['categories']['transactional_emails']['score'] < 70),
            'missing_mailbox' => $this->queue($locales, 'Missing mailbox experience', 'Mailbox flows need locale validation.', fn (Locale $locale): bool => $this->readiness->forLocale($locale)['categories']['mailbox_experience']['score'] < 70),
            'missing_compliance' => $this->queue($locales, 'Missing compliance readiness', 'Regional policy and trust readiness is incomplete.', fn (Locale $locale): bool => $this->readiness->forLocale($locale)['categories']['compliance']['score'] < 70),
        ];
    }

    /**
     * @param  callable(Locale): bool  $filter
     * @return array{label: string, description: string, count: int, locales: array<int, string>}
     */
    private function queue(Collection $locales, string $label, string $description, callable $filter): array
    {
        $matches = $locales
            ->filter($filter)
            ->map(fn (Locale $locale): string => $locale->language_name)
            ->values()
            ->all();

        return [
            'label' => $label,
            'description' => $description,
            'count' => count($matches),
            'locales' => array_slice($matches, 0, 5),
        ];
    }
}
