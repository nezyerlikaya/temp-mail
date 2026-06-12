<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\AkismetSpamService;
use App\Services\Security\SecuritySettingsStore;

class TestAkismetAction
{
    public function __construct(
        private readonly SecuritySettingsStore $store,
        private readonly AkismetSpamService $service,
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

        $this->store->recordTest('akismet', $result);
        $this->audit->record('security.akismet_tested', $actor, $actor, ['status' => $result['status']], [
            'module' => 'security',
            'action' => 'Akismet tested',
            'severity' => $result['status'] === 'failed' ? 'warning' : 'info',
        ]);

        return $result;
    }
}
