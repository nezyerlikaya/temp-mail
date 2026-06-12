<?php

namespace App\Services\Security;

class BotProtectionService
{
    public function __construct(private readonly SecuritySettingsStore $store) {}

    /** @return array{ready: bool, status: string, message: string} */
    public function readiness(): array
    {
        $settings = $this->store->group('bot_protection', true);

        if (! $settings['is_active']) {
            return ['ready' => true, 'status' => 'passive', 'message' => 'Bot protection is configured but currently passive.'];
        }

        if ($settings['provider'] === 'none') {
            return ['ready' => true, 'status' => 'passive', 'message' => 'No bot provider is active. Admin access remains available.'];
        }

        if (! filled($settings['secrets']['site_key'] ?? null) || ! filled($settings['secrets']['secret_key'] ?? null)) {
            return ['ready' => false, 'status' => 'needs_key', 'message' => 'Provider keys are required before activation can protect public forms.'];
        }

        return ['ready' => true, 'status' => 'configured', 'message' => 'Provider keys are present and ready for verification.'];
    }

    public function shouldProtect(string $form): bool
    {
        $settings = $this->store->bot();

        return (bool) $settings['is_active']
            && $settings['provider'] !== 'none'
            && in_array($form, $settings['protected_forms'] ?? [], true);
    }
}
