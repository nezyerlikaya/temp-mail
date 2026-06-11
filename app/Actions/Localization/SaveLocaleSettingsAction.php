<?php

namespace App\Actions\Localization;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Localization\LocaleSettingsStore;

class SaveLocaleSettingsAction
{
    public function __construct(
        private readonly LocaleSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, array<string, mixed>> $locales */
    public function handle(User $actor, array $locales): void
    {
        $this->store->save($locales, $actor);

        $this->audit->record('locale.settings_updated', $actor, null, [
            'locales' => array_keys($locales),
        ], ['module' => 'localization', 'action' => 'Update locale readiness', 'severity' => 'critical']);
    }

    /** @param array<int, string> $localeCodes */
    public function bulk(User $actor, array $localeCodes, string $action): void
    {
        $this->store->bulk($localeCodes, $action, $actor);

        $this->audit->record('locale.bulk_updated', $actor, null, [
            'locales' => $localeCodes,
            'bulk_action' => $action,
        ], ['module' => 'localization', 'action' => 'Bulk update locales', 'severity' => 'critical']);
    }

    public function updateStatus(User $actor, string $localeCode, string $action): void
    {
        $this->store->updateStatus($localeCode, $action, $actor);

        $this->audit->record('locale.status_updated', $actor, null, [
            'locale' => $localeCode,
            'status_action' => $action,
        ], ['module' => 'localization', 'action' => 'Update locale status', 'severity' => 'critical']);
    }
}
