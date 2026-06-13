<?php

namespace App\Services\Appearance;

use App\Services\Themes\ThemeRegistry;

class AppearanceTokenRegistry
{
    public const COLOR_PATTERN = '/^#[0-9a-fA-F]{6}$/';

    public function __construct(private readonly ThemeRegistry $themes) {}

    /** @return array<string, array{label: string, type: string}> */
    public function tokens(): array
    {
        return [
            'brand_color' => ['label' => 'Brand color', 'type' => 'color'],
            'accent_color' => ['label' => 'Accent color', 'type' => 'color'],
            'background_color' => ['label' => 'Background color', 'type' => 'color'],
            'surface_color' => ['label' => 'Surface color', 'type' => 'color'],
            'text_color' => ['label' => 'Text color', 'type' => 'color'],
            'muted_text_color' => ['label' => 'Muted text color', 'type' => 'color'],
            'border_color' => ['label' => 'Border color', 'type' => 'color'],
            'button_radius' => ['label' => 'Button radius', 'type' => 'radius'],
            'card_radius' => ['label' => 'Card radius', 'type' => 'radius'],
            'shadow_level' => ['label' => 'Shadow level', 'type' => 'shadow'],
            'motion_level' => ['label' => 'Motion level', 'type' => 'motion'],
        ];
    }

    /** @return array<string, array<string, string>> */
    public function defaults(): array
    {
        return [
            'horizon' => [
                'brand_color' => '#0f766e',
                'accent_color' => '#2563eb',
                'background_color' => '#f6f8fb',
                'surface_color' => '#ffffff',
                'text_color' => '#1c1917',
                'muted_text_color' => '#57534e',
                'border_color' => '#d6d3d1',
                'button_radius' => 'md',
                'card_radius' => 'lg',
                'shadow_level' => 'soft',
                'motion_level' => 'polished',
            ],
            'atlas' => [
                'brand_color' => '#4f46e5',
                'accent_color' => '#0891b2',
                'background_color' => '#111827',
                'surface_color' => '#1f2937',
                'text_color' => '#f8fafc',
                'muted_text_color' => '#cbd5e1',
                'border_color' => '#334155',
                'button_radius' => 'sm',
                'card_radius' => 'md',
                'shadow_level' => 'medium',
                'motion_level' => 'subtle',
            ],
            'legacy' => [
                'brand_color' => '#047857',
                'accent_color' => '#0f766e',
                'background_color' => '#ffffff',
                'surface_color' => '#f8fafc',
                'text_color' => '#1f2937',
                'muted_text_color' => '#64748b',
                'border_color' => '#e2e8f0',
                'button_radius' => 'sm',
                'card_radius' => 'sm',
                'shadow_level' => 'none',
                'motion_level' => 'none',
            ],
        ];
    }

    /** @return array<string, string> */
    public function defaultFor(string $theme): array
    {
        return $this->defaults()[$theme] ?? $this->defaults()[$this->themes->defaultSlug()];
    }

    /** @return array<int, string> */
    public function tokenNames(): array
    {
        return array_keys($this->tokens());
    }

    /** @return array<string, string> */
    public function radiusOptions(): array
    {
        return [
            'none' => '0px',
            'sm' => '4px',
            'md' => '8px',
            'lg' => '12px',
        ];
    }

    /** @return array<string, string> */
    public function shadowOptions(): array
    {
        return [
            'none' => 'none',
            'soft' => '0 12px 30px rgba(15, 23, 42, 0.10)',
            'medium' => '0 18px 48px rgba(15, 23, 42, 0.18)',
        ];
    }

    /** @return array<string, string> */
    public function motionOptions(): array
    {
        return [
            'none' => '0ms',
            'subtle' => '120ms',
            'polished' => '180ms',
        ];
    }

    public function isAllowedTheme(string $theme): bool
    {
        return in_array($theme, $this->themes->slugs(), true);
    }

    public function isAllowedToken(string $token): bool
    {
        return array_key_exists($token, $this->tokens());
    }
}
