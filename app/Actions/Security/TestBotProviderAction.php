<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\BotProtectionService;
use App\Services\Security\SecuritySettingsStore;

class TestBotProviderAction
{
    public function __construct(
        private readonly SecuritySettingsStore $store,
        private readonly BotProtectionService $service,
        private readonly AuditLogger $audit,
    ) {}

    /** @return array{status: string, message: string} */
    public function handle(User $actor): array
    {
        $readiness = $this->service->readiness();
        $result = [
            'status' => $readiness['ready'] ? 'ready' : 'failed',
            'message' => $readiness['message'],
        ];

        $this->store->recordTest('bot_protection', $result);
        $this->audit->record('security.bot_provider_tested', $actor, $actor, ['status' => $result['status']], [
            'module' => 'security',
            'action' => 'Bot provider tested',
            'severity' => $result['status'] === 'failed' ? 'warning' : 'info',
        ]);

        return $result;
    }
}
