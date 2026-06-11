<?php

namespace App\Services\Localization;

use App\Models\Locale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LocaleSettingsStore
{
    /** @return Collection<int, Locale> */
    public function all(): Collection
    {
        $this->ensureSeeded();

        return Locale::query()->orderBy('sort_order')->orderBy('language_name')->get();
    }

    public function ensureSeeded(): void
    {
        if (Locale::query()->exists()) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->defaults() as $locale) {
                Locale::query()->create($locale);
            }
        });
    }

    /**
     * @param  array<string, array<string, mixed>>  $locales
     */
    public function save(array $locales, User $actor): void
    {
        DB::transaction(function () use ($locales, $actor): void {
            if (collect($locales)->contains(fn (array $payload): bool => (bool) ($payload['is_default'] ?? false))) {
                Locale::query()->update(['is_default' => false]);
            }

            foreach ($locales as $localeCode => $payload) {
                Locale::query()
                    ->where('locale', $localeCode)
                    ->update([
                        'market_readiness' => $payload['market_readiness'],
                        'is_active' => (bool) ($payload['is_active'] ?? false),
                        'is_default' => (bool) ($payload['is_default'] ?? false),
                        'sort_order' => (int) $payload['sort_order'],
                        'launch_status' => $payload['launch_status'],
                        'updated_by' => $actor->id,
                    ]);
            }
        });
    }

    /**
     * @param  array<int, string>  $localeCodes
     */
    public function bulk(array $localeCodes, string $action, User $actor): void
    {
        $updates = match ($action) {
            'activate' => ['is_active' => true, 'launch_status' => 'ready', 'updated_by' => $actor->id],
            'deactivate' => ['is_active' => false, 'is_default' => false, 'launch_status' => 'draft', 'updated_by' => $actor->id],
            default => [],
        };

        if ($updates === []) {
            return;
        }

        DB::transaction(function () use ($localeCodes, $updates): void {
            Locale::query()->whereIn('locale', $localeCodes)->update($updates);

            if (! Locale::query()->where('is_default', true)->where('is_active', true)->exists()) {
                Locale::query()->where('locale', 'en')->update([
                    'is_active' => true,
                    'is_default' => true,
                    'launch_status' => 'launched',
                ]);
            }
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function defaults(): array
    {
        return [
            ['language_name' => 'English', 'native_name' => 'English', 'locale' => 'en', 'direction' => 'ltr', 'region' => 'Global', 'market_readiness' => 'ready', 'is_active' => true, 'is_default' => true, 'sort_order' => 1, 'launch_status' => 'launched'],
            ['language_name' => 'German', 'native_name' => 'Deutsch', 'locale' => 'de', 'direction' => 'ltr', 'region' => 'DACH', 'market_readiness' => 'ready', 'is_active' => true, 'is_default' => false, 'sort_order' => 2, 'launch_status' => 'ready'],
            ['language_name' => 'French', 'native_name' => 'Français', 'locale' => 'fr', 'direction' => 'ltr', 'region' => 'France', 'market_readiness' => 'ready', 'is_active' => true, 'is_default' => false, 'sort_order' => 3, 'launch_status' => 'ready'],
            ['language_name' => 'Spanish', 'native_name' => 'Español', 'locale' => 'es', 'direction' => 'ltr', 'region' => 'Spain and LATAM', 'market_readiness' => 'ready', 'is_active' => true, 'is_default' => false, 'sort_order' => 4, 'launch_status' => 'ready'],
            ['language_name' => 'Italian', 'native_name' => 'Italiano', 'locale' => 'it', 'direction' => 'ltr', 'region' => 'Italy', 'market_readiness' => 'ready', 'is_active' => true, 'is_default' => false, 'sort_order' => 5, 'launch_status' => 'ready'],
            ['language_name' => 'Dutch', 'native_name' => 'Nederlands', 'locale' => 'nl', 'direction' => 'ltr', 'region' => 'Netherlands', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 6, 'launch_status' => 'draft'],
            ['language_name' => 'Portuguese', 'native_name' => 'Português', 'locale' => 'pt', 'direction' => 'ltr', 'region' => 'Portugal and Brazil', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 7, 'launch_status' => 'draft'],
            ['language_name' => 'Polish', 'native_name' => 'Polski', 'locale' => 'pl', 'direction' => 'ltr', 'region' => 'Poland', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 8, 'launch_status' => 'draft'],
            ['language_name' => 'Swedish', 'native_name' => 'Svenska', 'locale' => 'sv', 'direction' => 'ltr', 'region' => 'Nordics', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 9, 'launch_status' => 'draft'],
            ['language_name' => 'Norwegian', 'native_name' => 'Norsk', 'locale' => 'no', 'direction' => 'ltr', 'region' => 'Norway', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 10, 'launch_status' => 'draft'],
            ['language_name' => 'Danish', 'native_name' => 'Dansk', 'locale' => 'da', 'direction' => 'ltr', 'region' => 'Denmark', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 11, 'launch_status' => 'draft'],
            ['language_name' => 'Finnish', 'native_name' => 'Suomi', 'locale' => 'fi', 'direction' => 'ltr', 'region' => 'Finland', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 12, 'launch_status' => 'draft'],
            ['language_name' => 'Czech', 'native_name' => 'Čeština', 'locale' => 'cs', 'direction' => 'ltr', 'region' => 'Czechia', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 13, 'launch_status' => 'draft'],
            ['language_name' => 'Hungarian', 'native_name' => 'Magyar', 'locale' => 'hu', 'direction' => 'ltr', 'region' => 'Hungary', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 14, 'launch_status' => 'draft'],
            ['language_name' => 'Romanian', 'native_name' => 'Română', 'locale' => 'ro', 'direction' => 'ltr', 'region' => 'Romania', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 15, 'launch_status' => 'draft'],
            ['language_name' => 'Greek', 'native_name' => 'Ελληνικά', 'locale' => 'el', 'direction' => 'ltr', 'region' => 'Greece', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 16, 'launch_status' => 'draft'],
            ['language_name' => 'Turkish', 'native_name' => 'Türkçe', 'locale' => 'tr', 'direction' => 'ltr', 'region' => 'Turkey', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 17, 'launch_status' => 'draft'],
            ['language_name' => 'Ukrainian', 'native_name' => 'Українська', 'locale' => 'uk', 'direction' => 'ltr', 'region' => 'Ukraine', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 18, 'launch_status' => 'draft'],
            ['language_name' => 'Russian', 'native_name' => 'Русский', 'locale' => 'ru', 'direction' => 'ltr', 'region' => 'Eastern Europe', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 19, 'launch_status' => 'draft'],
            ['language_name' => 'Arabic', 'native_name' => 'العربية', 'locale' => 'ar', 'direction' => 'rtl', 'region' => 'MENA', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 20, 'launch_status' => 'draft'],
            ['language_name' => 'Hebrew', 'native_name' => 'עברית', 'locale' => 'he', 'direction' => 'rtl', 'region' => 'Israel', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 21, 'launch_status' => 'draft'],
            ['language_name' => 'Hindi', 'native_name' => 'हिन्दी', 'locale' => 'hi', 'direction' => 'ltr', 'region' => 'India', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 22, 'launch_status' => 'draft'],
            ['language_name' => 'Bengali', 'native_name' => 'বাংলা', 'locale' => 'bn', 'direction' => 'ltr', 'region' => 'Bangladesh and India', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 23, 'launch_status' => 'draft'],
            ['language_name' => 'Chinese Simplified', 'native_name' => '简体中文', 'locale' => 'zh-CN', 'direction' => 'ltr', 'region' => 'China', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 24, 'launch_status' => 'draft'],
            ['language_name' => 'Japanese', 'native_name' => '日本語', 'locale' => 'ja', 'direction' => 'ltr', 'region' => 'Japan', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 25, 'launch_status' => 'draft'],
            ['language_name' => 'Korean', 'native_name' => '한국어', 'locale' => 'ko', 'direction' => 'ltr', 'region' => 'Korea', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 26, 'launch_status' => 'draft'],
            ['language_name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'locale' => 'id', 'direction' => 'ltr', 'region' => 'Indonesia', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 27, 'launch_status' => 'draft'],
            ['language_name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'locale' => 'vi', 'direction' => 'ltr', 'region' => 'Vietnam', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 28, 'launch_status' => 'draft'],
            ['language_name' => 'Thai', 'native_name' => 'ไทย', 'locale' => 'th', 'direction' => 'ltr', 'region' => 'Thailand', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 29, 'launch_status' => 'draft'],
            ['language_name' => 'Swahili', 'native_name' => 'Kiswahili', 'locale' => 'sw', 'direction' => 'ltr', 'region' => 'East Africa', 'market_readiness' => 'planned', 'is_active' => false, 'is_default' => false, 'sort_order' => 30, 'launch_status' => 'draft'],
        ];
    }
}
