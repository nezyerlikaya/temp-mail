<?php

namespace App\Actions\Localization;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Localization\LocaleSettingsStore;

class LocaleBulkAction
{
    public function __construct(
        private readonly LocaleSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<int, string> $localeCodes */
    public function handle(User $actor, array $localeCodes, string $action): void
    {
        $this->store->bulk($localeCodes, $action, $actor);

        $this->audit->record('locale.bulk_updated', $actor, null, [
            'locales' => $localeCodes,
            'bulk_action' => $action,
        ], ['module' => 'localization', 'action' => 'Bulk update locales', 'severity' => 'critical']);
    }
}
