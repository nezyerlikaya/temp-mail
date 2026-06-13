<?php

namespace App\Services\Themes;

use App\Models\ThemeState;
use Illuminate\Support\Collection;

class ThemeManager
{
    public function __construct(private readonly ThemeRegistry $registry) {}

    /** @return Collection<int, array<string, mixed>> */
    public function cards(): Collection
    {
        $this->ensureRegisteredThemes();
        $states = ThemeState::query()->whereIn('slug', $this->registry->slugs())->get()->keyBy('slug');

        return $this->registry->collection()
            ->map(function (array $theme) use ($states): array {
                /** @var ThemeState|null $state */
                $state = $states->get($theme['slug']);

                return [
                    ...$theme,
                    'status' => $state?->status ?? 'inactive',
                    'is_active' => $state?->status === 'active',
                    'last_activated_at' => $state?->last_activated_at,
                    'activated_by' => $state?->activated_by,
                ];
            })
            ->values();
    }

    public function active(): ThemeState
    {
        $this->ensureRegisteredThemes();

        return ThemeState::query()->where('status', 'active')->first()
            ?? $this->activateDefaultState();
    }

    public function previous(): ?ThemeState
    {
        $this->ensureRegisteredThemes();

        return ThemeState::query()
            ->whereIn('slug', $this->registry->slugs())
            ->where('status', 'inactive')
            ->whereNotNull('last_deactivated_at')
            ->latest('last_deactivated_at')
            ->first();
    }

    /** @return array{ready: bool, slug: string|null, name: string|null, message: string} */
    public function rollbackReadiness(): array
    {
        $previous = $this->previous();
        $theme = $previous ? $this->registry->find($previous->slug) : null;

        return [
            'ready' => $previous !== null && $theme !== null,
            'slug' => $previous?->slug,
            'name' => $theme['name'] ?? null,
            'message' => $previous && $theme
                ? 'Previous theme can be restored by activating '.$theme['name'].'.'
                : 'Rollback becomes ready after the first successful theme change.',
        ];
    }

    public function ensureRegisteredThemes(): void
    {
        foreach ($this->registry->all() as $theme) {
            ThemeState::query()->firstOrCreate(
                ['slug' => $theme['slug']],
                [
                    'status' => $theme['slug'] === $this->registry->defaultSlug() ? 'active' : 'inactive',
                    'last_activated_at' => $theme['slug'] === $this->registry->defaultSlug() ? now() : null,
                ],
            );
        }

        ThemeState::query()
            ->whereNotIn('slug', $this->registry->slugs())
            ->update(['status' => 'inactive']);

        if (ThemeState::query()->whereIn('slug', $this->registry->slugs())->where('status', 'active')->count() !== 1) {
            $this->repairActiveState();
        }
    }

    private function repairActiveState(): void
    {
        $active = ThemeState::query()
            ->whereIn('slug', $this->registry->slugs())
            ->where('status', 'active')
            ->latest('last_activated_at')
            ->first();

        if (! $active) {
            $active = $this->activateDefaultState();
        }

        ThemeState::query()
            ->whereIn('slug', $this->registry->slugs())
            ->whereKeyNot($active->getKey())
            ->update(['status' => 'inactive']);
    }

    private function activateDefaultState(): ThemeState
    {
        $default = ThemeState::query()->firstOrCreate(['slug' => $this->registry->defaultSlug()]);
        $default->forceFill([
            'status' => 'active',
            'last_activated_at' => $default->last_activated_at ?? now(),
        ])->save();

        return $default;
    }
}
