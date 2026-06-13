<?php

namespace App\Services\Themes;

use Illuminate\Support\Facades\Cache;

class ThemeResolver
{
    public function __construct(
        private readonly ThemeManager $manager,
        private readonly ThemeRegistry $registry,
    ) {}

    /** @return array<string, mixed> */
    public function active(): array
    {
        return Cache::remember(ThemeCacheService::ACTIVE_THEME_KEY, now()->addMinutes(30), function (): array {
            $state = $this->manager->active();
            $theme = $this->registry->find($state->slug) ?? $this->registry->find($this->registry->defaultSlug());

            return [
                ...$theme,
                'status' => $state->status,
                'last_activated_at' => $state->last_activated_at,
            ];
        });
    }
}
