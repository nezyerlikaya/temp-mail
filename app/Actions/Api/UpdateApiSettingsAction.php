<?php

namespace App\Actions\Api;

use App\Models\User;
use App\Services\Api\ApiSettingsStore;
use App\Services\Audit\AuditLogger;

class UpdateApiSettingsAction
{
    public function __construct(private readonly ApiSettingsStore $settings, private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): array
    {
        $settings = $this->settings->put($data, $actor);

        $this->audit->record('api_access.global_status_changed', $actor, null, [
            'api_enabled' => $settings['api_enabled'],
            'free_api_enabled' => $settings['free_api_enabled'],
            'premium_api_enabled' => $settings['premium_api_enabled'],
            'business_api_enabled' => $settings['business_api_enabled'],
        ], ['module' => 'api-access', 'action' => 'Global status changed']);

        return $settings;
    }
}
