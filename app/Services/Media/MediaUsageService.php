<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaUsage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class MediaUsageService
{
    /** @return Collection<int, MediaUsage> */
    public function forAsset(MediaAsset $asset): Collection
    {
        if (! $this->tableReady()) {
            return new Collection;
        }

        return $asset->usages()
            ->latest()
            ->get();
    }

    /** @return array{total: int, by_module: array<string, int>, orphaned: bool, readiness: array<int, array{label: string, module: string, count: int, status: string}>} */
    public function summary(MediaAsset $asset): array
    {
        $usages = $this->forAsset($asset);
        $byModule = $usages
            ->groupBy('module')
            ->map(fn (Collection $items): int => $items->count())
            ->all();

        return [
            'total' => $usages->count(),
            'by_module' => $byModule,
            'orphaned' => $usages->isEmpty(),
            'readiness' => $this->readiness($byModule),
        ];
    }

    /** @return array<int, array{label: string, module: string, count: int, status: string}> */
    public function readiness(array $byModule = []): array
    {
        return collect([
            'blog' => 'Blog posts readiness',
            'pages' => 'Pages readiness',
            'sections' => 'Sections readiness',
            'seo' => 'SEO/OG readiness',
            'avatars' => 'Avatars readiness',
            'email_templates' => 'Email templates readiness',
        ])->map(fn (string $label, string $module): array => [
            'label' => $label,
            'module' => $module,
            'count' => (int) ($byModule[$module] ?? 0),
            'status' => ((int) ($byModule[$module] ?? 0)) > 0 ? 'tracked' : 'ready',
        ])->values()->all();
    }

    public function orphanedCount(): int
    {
        if (! $this->tableReady()) {
            return MediaAsset::query()->count();
        }

        return MediaAsset::query()
            ->whereDoesntHave('usages')
            ->count();
    }

    public function tableReady(): bool
    {
        return Schema::hasTable('media_usages');
    }
}
