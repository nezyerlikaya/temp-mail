<?php

namespace App\Actions\Appearance;

use App\Models\AppearanceSetting;
use App\Models\User;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Audit\AuditLogger;

class UpdateAppearanceDraftAction
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, string $theme, array $payload): AppearanceSetting
    {
        $setting = $this->store->updateDraft(
            $theme,
            $payload['tokens'] ?? [],
            (string) ($payload['mode'] ?? 'custom'),
            $actor,
        );

        $this->audit->record('appearance.draft_updated', $actor, null, [
            'theme' => $theme,
            'mode' => $setting->mode,
            'token_count' => count($setting->draft_tokens ?? []),
        ], ['module' => 'appearance', 'action' => 'Draft tokens updated']);

        return $setting;
    }
}
