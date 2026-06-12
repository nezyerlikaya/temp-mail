<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Security\SecuritySettingsStore;

class UpdateBotProtectionAction
{
    public function __construct(
        private readonly SecuritySettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): void
    {
        $payload = [
            'provider' => $data['provider'],
            'recaptcha_mode' => $data['recaptcha_mode'] ?? 'v2_checkbox',
            'minimum_score' => (float) ($data['minimum_score'] ?? 0.5),
            'fail_mode' => $data['fail_mode'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'protected_forms' => $data['protected_forms'] ?? [],
        ];

        if ($payload['provider'] === 'none') {
            $payload['is_active'] = false;
        }

        $this->store->put('bot_protection', $payload, [
            'site_key' => $data['site_key'] ?? null,
            'secret_key' => $data['secret_key'] ?? null,
        ], $actor);

        $this->audit->record('security.bot_protection_updated', $actor, $actor, [
            'provider' => $payload['provider'],
            'recaptcha_mode' => $payload['recaptcha_mode'],
            'minimum_score' => $payload['minimum_score'],
            'fail_mode' => $payload['fail_mode'],
            'is_active' => $payload['is_active'],
            'protected_forms' => $payload['protected_forms'],
        ], [
            'module' => 'security',
            'action' => 'Bot protection updated',
            'severity' => 'critical',
        ]);
    }
}
