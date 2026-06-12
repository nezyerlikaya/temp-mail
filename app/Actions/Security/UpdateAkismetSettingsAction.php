<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\SecuritySettingsStore;

class UpdateAkismetSettingsAction
{
    public function __construct(
        private readonly SecuritySettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): void
    {
        $payload = [
            'site_url' => $data['site_url'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'protected_comments' => (bool) ($data['protected_comments'] ?? false),
            'contact_form_readiness' => (bool) ($data['contact_form_readiness'] ?? false),
            'mode' => $data['mode'],
        ];

        $this->store->put('akismet', $payload, ['api_key' => $data['api_key'] ?? null], $actor);

        $this->audit->record('security.akismet_updated', $actor, $actor, $payload, [
            'module' => 'security',
            'action' => 'Akismet updated',
            'severity' => 'critical',
        ]);
    }
}
