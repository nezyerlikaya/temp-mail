<?php

namespace App\Services\Typography;

use Illuminate\Support\Collection;

class FontRegistry
{
    /** @return array<string, string> */
    public function providers(): array
    {
        return [
            'system' => 'System',
            'local' => 'Local / self-hosted',
            'google_build' => 'Google Fonts registry',
        ];
    }

    /** @return array<string, string> */
    public function categories(): array
    {
        return [
            'sans' => 'Sans serif',
            'serif' => 'Serif',
            'mono' => 'Monospace',
            'display' => 'Display',
        ];
    }

    /** @return array<string, string> */
    public function usageScopes(): array
    {
        return [
            'ui' => 'UI',
            'heading' => 'Heading',
            'body' => 'Body',
            'mono' => 'Mono',
        ];
    }

    /** @return array<string, string> */
    public function scripts(): array
    {
        return [
            'latin' => 'Latin',
            'latin_extended' => 'Latin Extended',
            'cyrillic' => 'Cyrillic',
            'greek' => 'Greek',
            'arabic' => 'Arabic',
            'hebrew' => 'Hebrew',
        ];
    }

    /** @return array<int, string> */
    public function fontDisplayOptions(): array
    {
        return ['swap', 'optional'];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function families(): Collection
    {
        return collect([
            $this->family('plus-jakarta-sans', 'Plus Jakarta Sans', 'Plus Jakarta Sans', 'google_build', 'sans', ['latin', 'latin_extended'], false, [400, 500, 600, 700, 800]),
            $this->family('inter', 'Inter', 'Inter', 'google_build', 'sans', ['latin', 'latin_extended', 'cyrillic', 'greek'], false, [400, 500, 600, 700, 800]),
            $this->family('manrope', 'Manrope', 'Manrope', 'google_build', 'sans', ['latin', 'latin_extended', 'cyrillic'], false, [400, 500, 600, 700, 800]),
            $this->family('ibm-plex-sans-arabic', 'IBM Plex Sans Arabic', 'IBM Plex Sans Arabic', 'google_build', 'sans', ['arabic', 'latin'], true, [400, 500, 600, 700]),
            $this->family('noto-sans-arabic', 'Noto Sans Arabic', 'Noto Sans Arabic', 'google_build', 'sans', ['arabic', 'latin'], true, [400, 500, 600, 700]),
            $this->family('noto-sans-hebrew', 'Noto Sans Hebrew', 'Noto Sans Hebrew', 'google_build', 'sans', ['hebrew', 'latin'], true, [400, 500, 600, 700]),
            $this->family('assistant', 'Assistant', 'Assistant', 'google_build', 'sans', ['hebrew', 'latin'], true, [400, 500, 600, 700]),
            $this->family('noto-sans', 'Noto Sans', 'Noto Sans', 'google_build', 'sans', ['latin', 'latin_extended', 'cyrillic', 'greek'], false, [400, 500, 600, 700]),
            $this->family('jetbrains-mono', 'JetBrains Mono', 'JetBrains Mono', 'google_build', 'mono', ['latin', 'latin_extended', 'cyrillic', 'greek'], false, [400, 500, 600, 700]),
            $this->family('system-sans', 'System Sans', 'system-ui', 'system', 'sans', ['latin', 'latin_extended', 'cyrillic', 'greek'], false, [400, 500, 600, 700], true),
            $this->family('system-mono', 'System Mono', 'ui-monospace', 'system', 'mono', ['latin', 'latin_extended', 'cyrillic', 'greek'], false, [400, 500, 600, 700], true),
        ]);
    }

    /** @return array<string, mixed>|null */
    public function find(string $slug): ?array
    {
        return $this->families()->firstWhere('slug', $slug);
    }

    /** @return array<int, string> */
    public function slugs(): array
    {
        return $this->families()->pluck('slug')->all();
    }

    /** @return array<int, string> */
    public function safeFallbacks(): array
    {
        return ['system-sans', 'system-mono', 'sans-serif', 'serif', 'monospace', 'ui-sans-serif', 'ui-monospace', 'Arial', 'Helvetica', 'Tahoma'];
    }

    /** @return array<string, string> */
    public function defaultAssignments(): array
    {
        return [
            'ui' => 'plus-jakarta-sans',
            'heading' => 'plus-jakarta-sans',
            'body' => 'inter',
            'mono' => 'jetbrains-mono',
        ];
    }

    /** @return array<string, array<string, string>> */
    public function themePresets(): array
    {
        return [
            'horizon' => ['ui' => 'plus-jakarta-sans', 'heading' => 'plus-jakarta-sans', 'body' => 'inter', 'mono' => 'jetbrains-mono'],
            'atlas' => ['ui' => 'inter', 'heading' => 'inter', 'body' => 'inter', 'mono' => 'jetbrains-mono'],
            'legacy' => ['ui' => 'manrope', 'heading' => 'manrope', 'body' => 'system-sans', 'mono' => 'system-mono'],
        ];
    }

    /** @return array<string, array<string, string>> */
    public function localePresets(): array
    {
        return [
            'ar' => ['ui' => 'ibm-plex-sans-arabic', 'heading' => 'ibm-plex-sans-arabic', 'body' => 'noto-sans-arabic', 'mono' => 'system-mono'],
            'he' => ['ui' => 'noto-sans-hebrew', 'heading' => 'noto-sans-hebrew', 'body' => 'assistant', 'mono' => 'system-mono'],
            'el' => ['ui' => 'inter', 'heading' => 'inter', 'body' => 'noto-sans', 'mono' => 'jetbrains-mono'],
            'ru' => ['ui' => 'inter', 'heading' => 'inter', 'body' => 'noto-sans', 'mono' => 'jetbrains-mono'],
        ];
    }

    /**
     * @param  array<int, string>  $scripts
     * @param  array<int, int>  $weights
     * @return array<string, mixed>
     */
    private function family(string $slug, string $name, string $cssFamily, string $provider, string $category, array $scripts, bool $rtl, array $weights, bool $ready = false): array
    {
        return [
            'slug' => $slug,
            'name' => $name,
            'css_family' => $cssFamily,
            'provider' => $provider,
            'category' => $category,
            'supported_scripts' => $scripts,
            'rtl_support' => $rtl,
            'available_weights' => $weights,
            'font_display' => 'swap',
            'is_active' => true,
            'local_file_ready' => $ready,
            'media_ready' => $ready,
            'metadata' => ['source' => $provider === 'google_build' ? 'build-managed registry entry' : 'runtime-safe system family'],
        ];
    }
}
