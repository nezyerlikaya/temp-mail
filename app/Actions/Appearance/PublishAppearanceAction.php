<?php

namespace App\Actions\Appearance;

use App\Models\AppearanceVersion;
use App\Models\User;
use App\Services\Appearance\AppearanceContrastService;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Appearance\AppearanceVersionService;
use App\Services\Audit\AuditLogger;
use InvalidArgumentException;

class PublishAppearanceAction
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AppearanceContrastService $contrast,
        private readonly AppearanceVersionService $versions,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $theme): AppearanceVersion
    {
        $tokens = $this->store->draftTokens($theme);
        $report = $this->contrast->report($tokens);

        if (! $report['summary']['publishable']) {
            throw new InvalidArgumentException('Critical contrast failures must be fixed before publishing appearance tokens.');
        }

        $this->store->publish($theme, $tokens, $actor);
        $version = $this->versions->create($theme, $tokens, $report, $actor);

        $this->audit->record('appearance.published', $actor, null, [
            'theme' => $theme,
            'version' => $version->version_number,
            'warnings' => $report['summary']['warnings'],
        ], ['module' => 'appearance', 'action' => 'Appearance published']);

        return $version;
    }
}
