<?php

namespace App\Actions\Appearance;

use App\Models\AppearanceVersion;
use App\Models\User;
use App\Services\Appearance\AppearanceContrastService;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Appearance\AppearanceVersionService;
use App\Services\Audit\AuditLogger;
use InvalidArgumentException;

class RollbackAppearanceAction
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AppearanceContrastService $contrast,
        private readonly AppearanceVersionService $versions,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $theme, AppearanceVersion $version): AppearanceVersion
    {
        if ($version->theme_slug !== $theme) {
            throw new InvalidArgumentException('The selected version does not belong to this theme.');
        }

        $tokens = $version->tokens;
        $report = $this->contrast->report($tokens);

        if (! $report['summary']['publishable']) {
            throw new InvalidArgumentException('This version cannot be restored because it now fails critical contrast checks.');
        }

        $this->store->restorePublished($theme, $tokens, $actor);
        $restored = $this->versions->create($theme, $tokens, $report, $actor, $version);

        $this->audit->record('appearance.rolled_back', $actor, null, [
            'theme' => $theme,
            'from_version' => $version->version_number,
            'new_version' => $restored->version_number,
        ], ['module' => 'appearance', 'action' => 'Appearance rolled back']);

        return $restored;
    }
}
