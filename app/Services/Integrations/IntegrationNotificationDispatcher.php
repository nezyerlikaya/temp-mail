<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;
use App\Services\Notifications\NotificationService;

class IntegrationNotificationDispatcher
{
    public function __construct(private readonly NotificationService $notifications) {}

    /** @param array<string, mixed> $result */
    public function dispatchIfNeeded(IntegrationSetting $setting, array $result): void
    {
        if (! in_array($result['status'] ?? null, ['failed', 'degraded'], true)) {
            return;
        }

        $this->notifications->dispatch([
            'event_key' => 'integration_connection_'.$result['status'],
            'title' => str($setting->integration_key)->replace('_', ' ')->headline().' connection needs attention',
            'message' => (string) ($result['message'] ?? 'Integration connection readiness needs attention.'),
            'severity' => $result['status'] === 'failed' ? 'critical' : 'warning',
            'related_module' => 'billing',
            'action_route' => 'admin.integrations.index',
            'action_parameters' => ['integration' => $setting->integration_key, 'environment' => $setting->environment],
        ], sendEmail: false);
    }
}
