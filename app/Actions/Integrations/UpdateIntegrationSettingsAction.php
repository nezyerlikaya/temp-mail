<?php

namespace App\Actions\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\IntegrationSettingsStore;

class UpdateIntegrationSettingsAction
{
    public function __construct(
        private readonly IntegrationSettingsStore $settings,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, string $key, string $environment, array $data): IntegrationSetting
    {
        $setting = $this->settings->update($key, $environment, $data, $actor);

        $this->audit->record('integrations.settings_updated', $actor, null, [
            'integration_key' => $key,
            'environment' => $environment,
            'configured_fields' => array_keys($setting->payload ?? []),
            'secret_fields_replaced' => array_keys(array_filter($data['secrets'] ?? [], fn (mixed $value): bool => filled($value))),
        ], ['module' => 'integrations', 'target' => $setting]);

        return $setting;
    }
}
