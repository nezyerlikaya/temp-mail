<?php

namespace App\Actions\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\IntegrationSettingsStore;

class DeactivateIntegrationAction
{
    public function __construct(
        private readonly IntegrationSettingsStore $settings,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $key, string $environment): IntegrationSetting
    {
        $setting = $this->settings->deactivate($key, $environment, $actor);

        $this->audit->record('integrations.deactivated', $actor, null, [
            'integration_key' => $key,
            'environment' => $environment,
        ], ['module' => 'integrations', 'target' => $setting]);

        return $setting;
    }
}
