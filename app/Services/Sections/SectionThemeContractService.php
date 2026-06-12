<?php

namespace App\Services\Sections;

class SectionThemeContractService
{
    /** @return array<int, array<string, mixed>> */
    public function readiness(): array
    {
        return collect(['Horizon', 'Atlas', 'Legacy'])->map(fn (string $theme): array => [
            'theme' => $theme,
            'state' => 'ready',
            'message' => $theme.' can resolve active language and placement scoped sections.',
            'supported_types' => [
                'cta',
                'faq',
                'blog_teaser',
                'feature_grid',
                'trust_security',
                'abuse_notice',
                'cookie_notice',
            ],
        ])->all();
    }
}
