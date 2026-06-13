<?php

namespace App\Services\Appearance;

use App\Services\Themes\ThemeResolver;
use Illuminate\Support\Facades\Cache;

class AppearanceTokenResolver
{
    public function __construct(
        private readonly AppearanceSettingsStore $store,
        private readonly AppearanceCssVariableResolver $css,
        private readonly ThemeResolver $themes,
    ) {}

    /** @return array{theme: string, tokens: array<string, string>, variables: array<string, string>, inline_style: string} */
    public function activePublicTokens(): array
    {
        $theme = $this->themes->active()['slug'];

        return $this->forTheme($theme);
    }

    /** @return array{theme: string, tokens: array<string, string>, variables: array<string, string>, inline_style: string} */
    public function forTheme(string $theme): array
    {
        return Cache::rememberForever('appearance.tokens.'.$theme, function () use ($theme): array {
            $tokens = $this->store->publishedTokens($theme);

            return [
                'theme' => $theme,
                'tokens' => $tokens,
                'variables' => $this->css->variables($tokens),
                'inline_style' => $this->css->inlineStyle($tokens),
            ];
        });
    }
}
