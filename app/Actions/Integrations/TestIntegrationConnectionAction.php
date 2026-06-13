<?php

namespace App\Actions\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Integrations\IntegrationClientRegistry;
use App\Services\Integrations\IntegrationEventLogger;
use App\Services\Integrations\IntegrationNotificationDispatcher;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\IntegrationSecretStore;

class TestIntegrationConnectionAction
{
    public function __construct(
        private readonly IntegrationRegistry $registry,
        private readonly IntegrationSecretStore $secrets,
        private readonly IntegrationClientRegistry $clients,
        private readonly IntegrationEventLogger $events,
        private readonly IntegrationNotificationDispatcher $notifications,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $key, string $environment): IntegrationSetting
    {
        $definition = $this->registry->find($key);
        abort_unless($definition !== null, 404);

        $setting = IntegrationSetting::query()->firstOrCreate(
            ['integration_key' => $key, 'environment' => $environment],
            ['payload' => [], 'encrypted_secrets' => null, 'connection_status' => 'not_tested', 'updated_by' => $actor->id],
        );

        $started = microtime(true);
        $result = $this->clients
            ->testerFor($key)
            ->test($definition, $setting, $setting->payload ?? [], $this->secrets->decrypt($setting->encrypted_secrets));
        $durationMs = (int) round((microtime(true) - $started) * 1000);

        $setting = $this->events->record($setting, $result, $actor, $durationMs);
        $this->notifications->dispatchIfNeeded($setting, $result);

        $this->audit->record('integrations.connection_tested', $actor, null, $this->events->safeAuditPayload($setting, $result, $durationMs), [
            'module' => 'integrations',
            'target' => $setting,
            'severity' => in_array($setting->connection_status, ['failed', 'degraded'], true) ? 'warning' : 'info',
        ]);

        return $setting;
    }
}
