<?php

namespace App\Services\Appearance;

use App\Models\AppearanceSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class AppearanceSettingsStore
{
    public function __construct(private readonly AppearanceTokenRegistry $registry) {}

    public function setting(string $theme): AppearanceSetting
    {
        $this->assertTheme($theme);

        return AppearanceSetting::query()->firstOrCreate(
            ['theme_slug' => $theme],
            [
                'mode' => 'defaults',
                'draft_tokens' => $this->registry->defaultFor($theme),
                'published_tokens' => null,
            ],
        );
    }

    /** @return array<string, string> */
    public function draftTokens(string $theme): array
    {
        $setting = $this->setting($theme);

        return $this->mergeWithDefaults($theme, $setting->draft_tokens ?? []);
    }

    /** @return array<string, string> */
    public function publishedTokens(string $theme): array
    {
        $setting = $this->setting($theme);

        if ($setting->mode === 'defaults' || blank($setting->published_tokens)) {
            return $this->registry->defaultFor($theme);
        }

        return $this->mergeWithDefaults($theme, $setting->published_tokens ?? []);
    }

    /** @param array<string, mixed> $tokens */
    public function updateDraft(string $theme, array $tokens, string $mode, User $actor): AppearanceSetting
    {
        if (! in_array($mode, ['defaults', 'custom'], true)) {
            throw new InvalidArgumentException('Appearance mode is not allowlisted.');
        }

        $clean = $mode === 'defaults'
            ? $this->registry->defaultFor($theme)
            : $this->sanitize($tokens);

        $setting = $this->setting($theme);
        $setting->forceFill([
            'mode' => $mode,
            'draft_tokens' => $this->mergeWithDefaults($theme, $clean),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->forget($theme);

        return $setting;
    }

    public function resetTheme(string $theme, User $actor): AppearanceSetting
    {
        $setting = $this->setting($theme);
        $setting->forceFill([
            'mode' => 'defaults',
            'draft_tokens' => $this->registry->defaultFor($theme),
            'published_tokens' => null,
            'published_at' => null,
            'published_by' => null,
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->forget($theme);

        return $setting;
    }

    /** @param array<string, string> $tokens */
    public function publish(string $theme, array $tokens, User $actor): AppearanceSetting
    {
        $clean = $this->mergeWithDefaults($theme, $tokens);
        $setting = $this->setting($theme);
        $setting->forceFill([
            'mode' => 'custom',
            'draft_tokens' => $clean,
            'published_tokens' => $clean,
            'published_at' => now(),
            'published_by' => $actor->getKey(),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->forget($theme);

        return $setting;
    }

    /** @param array<string, string> $tokens */
    public function restorePublished(string $theme, array $tokens, User $actor): AppearanceSetting
    {
        return $this->publish($theme, $tokens, $actor);
    }

    public function resetToken(string $theme, string $token, User $actor): AppearanceSetting
    {
        if (! $this->registry->isAllowedToken($token)) {
            throw new InvalidArgumentException('The requested appearance token is not allowlisted.');
        }

        $setting = $this->setting($theme);
        $draft = $this->draftTokens($theme);
        $draft[$token] = $this->registry->defaultFor($theme)[$token];

        $setting->forceFill([
            'mode' => 'custom',
            'draft_tokens' => $draft,
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->forget($theme);

        return $setting;
    }

    /** @param array<string, mixed> $tokens @return array<string, string> */
    public function sanitize(array $tokens): array
    {
        $clean = [];

        foreach ($tokens as $name => $value) {
            if (! $this->registry->isAllowedToken((string) $name)) {
                throw new InvalidArgumentException('Unsafe appearance token ['.$name.'] was rejected.');
            }

            $token = $this->registry->tokens()[$name];
            $value = (string) $value;

            if ($token['type'] === 'color' && ! preg_match(AppearanceTokenRegistry::COLOR_PATTERN, $value)) {
                throw new InvalidArgumentException('Color token ['.$name.'] must use a safe #RRGGBB value.');
            }

            if ($token['type'] === 'radius' && ! array_key_exists($value, $this->registry->radiusOptions())) {
                throw new InvalidArgumentException('Radius token ['.$name.'] is not allowlisted.');
            }

            if ($token['type'] === 'shadow' && ! array_key_exists($value, $this->registry->shadowOptions())) {
                throw new InvalidArgumentException('Shadow token ['.$name.'] is not allowlisted.');
            }

            if ($token['type'] === 'motion' && ! array_key_exists($value, $this->registry->motionOptions())) {
                throw new InvalidArgumentException('Motion token ['.$name.'] is not allowlisted.');
            }

            $clean[$name] = $value;
        }

        return $clean;
    }

    private function assertTheme(string $theme): void
    {
        if (! $this->registry->isAllowedTheme($theme)) {
            throw new InvalidArgumentException('The requested theme is not registered.');
        }
    }

    /** @param array<string, mixed> $tokens @return array<string, string> */
    private function mergeWithDefaults(string $theme, array $tokens): array
    {
        return [...$this->registry->defaultFor($theme), ...$this->sanitize($tokens)];
    }

    private function forget(string $theme): void
    {
        Cache::forget('appearance.tokens.'.$theme);
    }
}
