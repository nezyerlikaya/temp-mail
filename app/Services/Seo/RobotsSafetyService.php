<?php

namespace App\Services\Seo;

use App\Models\SeoRecord;

class RobotsSafetyService
{
    /** @return array{state: string, warnings: array<int, string>, noindex_count: int, total_count: int} */
    public function readiness(): array
    {
        $total = SeoRecord::query()->count();
        $noindex = SeoRecord::query()->where('robots_index', false)->count();
        $warnings = [];

        if ($total > 0 && $noindex === $total) {
            $warnings[] = 'Every SEO record is marked noindex. Publishing a blocking robots preset would hide the site from search.';
        } elseif ($noindex > 0) {
            $warnings[] = $noindex.' SEO targets are marked noindex. Review before launch.';
        }

        return [
            'state' => $warnings === [] ? 'ready' : 'warning',
            'warnings' => $warnings,
            'noindex_count' => $noindex,
            'total_count' => $total,
        ];
    }
}
