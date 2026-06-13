<?php

namespace App\Actions\Appearance;

use App\Models\AppearanceSetting;
use App\Models\User;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Audit\AuditLogger;

class ResetAppearanceAction
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $theme): AppearanceSetting
    {
        $setting = $this->store->resetTheme($theme, $actor);

        $this->audit->record('appearance.reset', $actor, null, [
            'theme' => $theme,
            'scope' => 'all_tokens',
        ], ['module' => 'appearance', 'action' => 'Appearance reset']);

        return $setting;
    }
}
