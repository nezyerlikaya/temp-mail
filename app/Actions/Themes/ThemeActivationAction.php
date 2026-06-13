<?php

namespace App\Actions\Themes;

use App\Models\ThemeState;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Themes\ThemeActivationLock;
use App\Services\Themes\ThemeCacheService;
use App\Services\Themes\ThemeManager;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ThemeActivationAction
{
    public function __construct(
        private readonly ThemeRegistry $registry,
        private readonly ThemeManager $manager,
        private readonly ThemeActivationLock $lock,
        private readonly ThemeCacheService $cache,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, string $slug): ThemeState
    {
        if (! $this->registry->exists($slug)) {
            throw new InvalidArgumentException('The requested theme is not registered.');
        }

        $this->lock->acquire('theme:'.$slug);

        try {
            $activated = DB::transaction(function () use ($actor, $slug): ThemeState {
                $this->manager->ensureRegisteredThemes();

                $current = ThemeState::query()->where('status', 'active')->lockForUpdate()->first();

                if ($current?->slug === $slug) {
                    throw new InvalidArgumentException('This theme is already active.');
                }

                ThemeState::query()
                    ->where('status', 'active')
                    ->update([
                        'status' => 'inactive',
                        'last_deactivated_at' => now(),
                    ]);

                $next = ThemeState::query()->where('slug', $slug)->lockForUpdate()->firstOrFail();
                $next->forceFill([
                    'status' => 'active',
                    'last_activated_at' => now(),
                    'activated_by' => $actor->getKey(),
                ])->save();

                $this->audit->record('theme.activated', $actor, null, [
                    'previous_theme' => $current?->slug,
                    'active_theme' => $slug,
                ], ['module' => 'themes', 'action' => 'Theme activated']);

                return $next;
            });

            $this->cache->clear();

            return $activated;
        } finally {
            $this->lock->release();
        }
    }
}
