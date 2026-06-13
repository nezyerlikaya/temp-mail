<?php

namespace App\Actions\Appearance;

use App\Models\AppearanceSetting;
use App\Models\User;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Audit\AuditLogger;

class ResetAppearanceTokenAction
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $theme, string $token): AppearanceSetting
    {
        $setting = $this->store->resetToken($theme, $token, $actor);

        $this->audit->record('appearance.token_reset', $actor, null, [
            'theme' => $theme,
            'token' => $token,
        ], ['module' => 'appearance', 'action' => 'Appearance token reset']);

        return $setting;
    }
}
