<?php

namespace App\Services\Security;

class AkismetSpamService
{
    public function __construct(private readonly SecuritySettingsStore $store) {}

    /** @return array{ready: bool, status: string, message: string} */
    public function readiness(): array
    {
        $settings = $this->store->group('akismet', true);

        if (! $settings['is_active']) {
            return ['ready' => true, 'status' => 'passive', 'message' => 'Akismet is passive. Comment moderation remains manual-ready.'];
        }

        if (! filled($settings['secrets']['api_key'] ?? null) || ! filled($settings['site_url'] ?? null)) {
            return ['ready' => false, 'status' => 'needs_key', 'message' => 'Akismet API key and site URL are required before activation.'];
        }

        return ['ready' => true, 'status' => 'configured', 'message' => 'Akismet settings are ready for connection testing.'];
    }
}
