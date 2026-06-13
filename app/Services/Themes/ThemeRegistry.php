<?php

namespace App\Services\Themes;

use Illuminate\Support\Collection;

class ThemeRegistry
{
    /** @return array<int, array{slug: string, name: string, description: string, version: string, version_readiness: string, preview_readiness: string, public_path: string}> */
    public function all(): array
    {
        return [
            [
                'slug' => 'horizon',
                'name' => 'Horizon',
                'description' => 'Default modern premium SaaS experience for high-trust public onboarding.',
                'version' => '1.0.0',
                'version_readiness' => 'Compatible',
                'preview_readiness' => 'Ready',
                'public_path' => 'themes.horizon',
            ],
            [
                'slug' => 'atlas',
                'name' => 'Atlas',
                'description' => 'Technical and developer-focused experience for API-heavy Temp Mail audiences.',
                'version' => '1.0.0',
                'version_readiness' => 'Compatible',
                'preview_readiness' => 'Ready',
                'public_path' => 'themes.atlas',
            ],
            [
                'slug' => 'legacy',
                'name' => 'Legacy',
                'description' => 'Lightweight shared-hosting-friendly public experience with minimal chrome.',
                'version' => '1.0.0',
                'version_readiness' => 'Compatible',
                'preview_readiness' => 'Ready',
                'public_path' => 'themes.legacy',
            ],
        ];
    }

    /** @return Collection<int, array<string, string>> */
    public function collection(): Collection
    {
        return collect($this->all());
    }

    /** @return array<string, string>|null */
    public function find(string $slug): ?array
    {
        return $this->collection()->firstWhere('slug', $slug);
    }

    public function exists(string $slug): bool
    {
        return $this->find($slug) !== null;
    }

    /** @return array<int, string> */
    public function slugs(): array
    {
        return $this->collection()->pluck('slug')->all();
    }

    public function defaultSlug(): string
    {
        return 'horizon';
    }
}
