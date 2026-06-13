<?php

namespace App\Services\Appearance;

class AppearanceCssVariableResolver
{
    public function __construct(private readonly AppearanceTokenRegistry $registry) {}

    /** @param array<string, string> $tokens @return array<string, string> */
    public function variables(array $tokens): array
    {
        return [
            '--tm-brand-color' => $tokens['brand_color'],
            '--tm-accent-color' => $tokens['accent_color'],
            '--tm-background-color' => $tokens['background_color'],
            '--tm-surface-color' => $tokens['surface_color'],
            '--tm-text-color' => $tokens['text_color'],
            '--tm-muted-text-color' => $tokens['muted_text_color'],
            '--tm-border-color' => $tokens['border_color'],
            '--tm-button-radius' => $this->registry->radiusOptions()[$tokens['button_radius']] ?? '8px',
            '--tm-card-radius' => $this->registry->radiusOptions()[$tokens['card_radius']] ?? '8px',
            '--tm-shadow' => $this->registry->shadowOptions()[$tokens['shadow_level']] ?? 'none',
            '--tm-motion-duration' => $this->registry->motionOptions()[$tokens['motion_level']] ?? '120ms',
        ];
    }

    /** @param array<string, string> $tokens */
    public function inlineStyle(array $tokens): string
    {
        return collect($this->variables($tokens))
            ->map(fn (string $value, string $name): string => $name.': '.$value)
            ->implode('; ');
    }
}
