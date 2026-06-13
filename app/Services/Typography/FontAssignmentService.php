<?php

namespace App\Services\Typography;

use App\Models\FontAssignment;
use App\Models\FontFamily;
use App\Models\Locale;
use App\Models\User;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FontAssignmentService
{
    public function __construct(
        private readonly FontRegistry $registry,
        private readonly ThemeRegistry $themes,
    ) {}

    public function ensureDefaults(): void
    {
        DB::transaction(function (): void {
            $this->registry->families()->each(function (array $family): void {
                $record = FontFamily::query()->firstOrCreate(
                    ['slug' => $family['slug']],
                    collect($family)->except('slug')->all(),
                );

                $record->forceFill(collect($family)
                    ->only(['name', 'css_family', 'provider', 'category', 'supported_scripts', 'rtl_support', 'available_weights', 'metadata'])
                    ->all())
                    ->save();
            });

            foreach ($this->registry->defaultAssignments() as $usage => $slug) {
                FontAssignment::query()->firstOrCreate(
                    ['scope' => 'global', 'scope_key' => 'default', 'usage' => $usage],
                    ['font_family_slug' => $slug, 'fallback_stack' => $this->fallbackFor($slug)],
                );
            }

            foreach ($this->registry->themePresets() as $theme => $assignments) {
                if (! $this->themes->exists($theme)) {
                    continue;
                }

                foreach ($assignments as $usage => $slug) {
                    FontAssignment::query()->firstOrCreate(
                        ['scope' => 'theme', 'scope_key' => $theme, 'usage' => $usage],
                        ['font_family_slug' => $slug, 'fallback_stack' => $this->fallbackFor($slug)],
                    );
                }
            }

            Locale::query()->orderBy('sort_order')->get()->each(function (Locale $locale): void {
                $preset = $this->localePresetFor($locale->locale);
                foreach ($preset as $usage => $slug) {
                    FontAssignment::query()->firstOrCreate(
                        ['scope' => 'locale', 'scope_key' => $locale->locale, 'usage' => $usage],
                        ['font_family_slug' => $slug, 'fallback_stack' => $this->fallbackFor($slug)],
                    );
                }
            });
        });
    }

    /** @return Collection<int, FontFamily> */
    public function families(): Collection
    {
        $this->ensureDefaults();

        return FontFamily::query()->orderByDesc('is_active')->orderBy('category')->orderBy('name')->get();
    }

    /** @return Collection<string, FontFamily> */
    public function activeFamilies(): Collection
    {
        $this->ensureDefaults();

        return FontFamily::query()->where('is_active', true)->orderBy('name')->get()->keyBy('slug');
    }

    /** @return Collection<int, FontAssignment> */
    public function assignments(): Collection
    {
        $this->ensureDefaults();

        return FontAssignment::query()->with('fontFamily')->orderBy('scope')->orderBy('scope_key')->orderBy('usage')->get();
    }

    public function assignmentFor(string $scope, string $scopeKey, string $usage): ?FontAssignment
    {
        $this->ensureDefaults();

        return FontAssignment::query()
            ->where('scope', $scope)
            ->where('scope_key', $scopeKey)
            ->where('usage', $usage)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateFamily(FontFamily $family, array $data, User $actor): FontFamily
    {
        $family->forceFill([
            'font_display' => $data['font_display'],
            'local_file_ready' => (bool) ($data['local_file_ready'] ?? false),
            'media_ready' => (bool) ($data['media_ready'] ?? false),
            'updated_by' => $actor->getKey(),
        ])->save();

        return $family;
    }

    public function setActive(FontFamily $family, bool $active, User $actor): FontFamily
    {
        $family->forceFill([
            'is_active' => $active,
            'updated_by' => $actor->getKey(),
        ])->save();

        return $family;
    }

    /**
     * @param  array<string, array{font_family_slug: string, fallback_stack?: array<int, string>}>  $assignments
     */
    public function updateAssignments(string $scope, string $scopeKey, array $assignments, User $actor): Collection
    {
        return DB::transaction(function () use ($scope, $scopeKey, $assignments, $actor): Collection {
            return collect($assignments)->map(function (array $assignment, string $usage) use ($scope, $scopeKey, $actor): FontAssignment {
                return $this->writeAssignment(
                    $scope,
                    $scopeKey,
                    $usage,
                    $assignment['font_family_slug'],
                    $this->normalizeFallback($assignment['fallback_stack'] ?? []),
                    $actor,
                );
            });
        });
    }

    /** @return array<int, string> */
    public function fallbackFor(string $slug): array
    {
        return match ($slug) {
            'jetbrains-mono', 'system-mono' => ['system-mono', 'ui-monospace', 'monospace'],
            'ibm-plex-sans-arabic', 'noto-sans-arabic' => ['noto-sans-arabic', 'system-sans', 'Tahoma', 'sans-serif'],
            'noto-sans-hebrew', 'assistant' => ['assistant', 'system-sans', 'Arial', 'sans-serif'],
            default => ['system-sans', 'ui-sans-serif', 'Arial', 'sans-serif'],
        };
    }

    /** @return array<string, string> */
    private function localePresetFor(string $locale): array
    {
        $key = str($locale)->before('-')->before('_')->lower()->toString();

        return $this->registry->localePresets()[$key] ?? [];
    }

    /**
     * @param  array<int, string>  $fallback
     * @return array<int, string>
     */
    private function normalizeFallback(array $fallback): array
    {
        $allowed = [...$this->registry->slugs(), ...$this->registry->safeFallbacks()];

        return collect($fallback)
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '' && in_array($value, $allowed, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $fallback
     */
    private function writeAssignment(string $scope, string $scopeKey, string $usage, string $slug, array $fallback, ?User $actor = null): FontAssignment
    {
        return FontAssignment::query()->updateOrCreate(
            ['scope' => $scope, 'scope_key' => $scopeKey, 'usage' => $usage],
            [
                'font_family_slug' => $slug,
                'fallback_stack' => $fallback === [] ? $this->fallbackFor($slug) : $fallback,
                'updated_by' => $actor?->getKey(),
            ],
        );
    }
}
