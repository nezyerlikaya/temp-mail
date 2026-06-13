<?php

namespace App\Actions\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\IntegrationSettingsStore;

class ActivateIntegrationAction
{
    public function __construct(
        private readonly IntegrationSettingsStore $settings,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $key, string $environment): IntegrationSetting
    {
        $setting = $this->settings->activate($key, $environment, $actor);

        $this->audit->record('integrations.activated', $actor, null, [
            'integration_key' => $key,
            'environment' => $environment,
        ], ['module' => 'integrations', 'target' => $setting]);

        return $setting;
    }
}
