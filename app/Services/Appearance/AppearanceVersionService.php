<?php

namespace App\Services\Appearance;

use App\Models\AppearanceVersion;
use App\Models\User;
use Illuminate\Support\Collection;

class AppearanceVersionService
{
    /** @param array<string, string> $tokens @param array<string, mixed> $contrastReport */
    public function create(string $theme, array $tokens, array $contrastReport, User $actor, ?AppearanceVersion $source = null): AppearanceVersion
    {
        return AppearanceVersion::query()->create([
            'theme_slug' => $theme,
            'version_number' => $this->nextNumber($theme),
            'tokens' => $tokens,
            'contrast_report' => $contrastReport,
            'published_by' => $actor->getKey(),
            'source_version_id' => $source?->getKey(),
        ]);
    }

    /** @return Collection<int, AppearanceVersion> */
    public function history(string $theme)
    {
        return AppearanceVersion::query()
            ->where('theme_slug', $theme)
            ->with('publisher')
            ->latest()
            ->limit(8)
            ->get();
    }

    private function nextNumber(string $theme): int
    {
        return ((int) AppearanceVersion::query()->where('theme_slug', $theme)->max('version_number')) + 1;
    }
}
